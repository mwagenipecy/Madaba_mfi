<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LoanArrearsCalculator
{
    /**
     * Recalculate arrears days on each schedule row and loan totals.
     *
     * Rules:
     * - Only one installment accrues live arrears days at a time (the current overdue one).
     * - When the next installment due date is reached, the previous unpaid installment stops
     *   counting and its arrears days are frozen (due period until next installment due date).
     * - Loan total arrears days = sum of frozen days on earlier unpaid installments + live days on the active installment.
     * - A day counts after the due date has passed (due today = 0 days; due yesterday unpaid = 1 day).
     */
    public function sync(Loan $loan): void
    {
        $loan->loadMissing(['schedules' => fn ($query) => $query->orderBy('installment_number')]);

        $schedules = $loan->schedules;
        if ($schedules->isEmpty()) {
            $this->resetLoanArrears($loan);
            return;
        }

        $today = Carbon::today();
        $totals = $this->calculate($schedules, $today);

        foreach ($totals['schedules'] as $scheduleId => $data) {
            /** @var LoanSchedule|null $schedule */
            $schedule = $schedules->firstWhere('id', $scheduleId);
            if (!$schedule) {
                continue;
            }

            $schedule->days_overdue = $data['days_overdue'];
            $schedule->outstanding_amount = $schedule->remaining_total;
            $this->applyScheduleStatus($schedule, $today);
            $schedule->save();
        }

        $loan->overdue_days = $totals['total_arrears_days'];
        $loan->overdue_amount = $totals['overdue_amount'];

        if ($totals['total_arrears_days'] > 0 && in_array($loan->status, ['active', 'disbursed'], true)) {
            $loan->status = 'overdue';
        } elseif ($totals['total_arrears_days'] === 0 && $loan->status === 'overdue') {
            $loan->status = 'active';
        }

        $loan->save();
    }

    /**
     * @return array{
     *     total_arrears_days: int,
     *     overdue_amount: float,
     *     schedules: array<int, array{days_overdue: int, is_active: bool, is_frozen: bool}>
     * }
     */
    public function calculate(Collection $schedules, ?Carbon $today = null): array
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $schedules = $schedules->sortBy('installment_number')->values();

        $unpaidPastDue = $schedules->filter(function (LoanSchedule $schedule) use ($today) {
            return $this->isUnpaid($schedule) && $schedule->due_date->lte($today);
        });

        $result = [
            'total_arrears_days' => 0,
            'overdue_amount' => 0.0,
            'schedules' => [],
        ];

        if ($unpaidPastDue->isEmpty()) {
            foreach ($schedules as $schedule) {
                $result['schedules'][$schedule->id] = [
                    'days_overdue' => 0,
                    'is_active' => false,
                    'is_frozen' => false,
                ];
            }

            return $result;
        }

        /** @var LoanSchedule $activeSchedule */
        $activeSchedule = $unpaidPastDue->last();
        $scheduleByNumber = $schedules->keyBy('installment_number');

        foreach ($schedules as $schedule) {
            if (!$this->isUnpaid($schedule)) {
                $result['schedules'][$schedule->id] = [
                    'days_overdue' => 0,
                    'is_active' => false,
                    'is_frozen' => false,
                ];
                continue;
            }

            if ($schedule->installment_number < $activeSchedule->installment_number) {
                $nextSchedule = $scheduleByNumber->get($schedule->installment_number + 1);
                $frozenDays = $this->frozenArrearsDays($schedule, $nextSchedule);
                $result['schedules'][$schedule->id] = [
                    'days_overdue' => $frozenDays,
                    'is_active' => false,
                    'is_frozen' => $frozenDays > 0,
                ];
                $result['total_arrears_days'] += $frozenDays;
                $result['overdue_amount'] += $schedule->remaining_total;
                continue;
            }

            if ($schedule->id === $activeSchedule->id) {
                $liveDays = $this->liveArrearsDays($schedule, $today);
                $result['schedules'][$schedule->id] = [
                    'days_overdue' => $liveDays,
                    'is_active' => $liveDays > 0,
                    'is_frozen' => false,
                ];
                $result['total_arrears_days'] += $liveDays;
                $result['overdue_amount'] += $schedule->remaining_total;
                continue;
            }

            $result['schedules'][$schedule->id] = [
                'days_overdue' => 0,
                'is_active' => false,
                'is_frozen' => false,
            ];
        }

        $result['overdue_amount'] = round($result['overdue_amount'], 2);

        return $result;
    }

    public function syncOrganizationLoans(?int $organizationId): void
    {
        if (!$organizationId) {
            return;
        }

        Loan::query()
            ->where('organization_id', $organizationId)
            ->whereIn('status', ['active', 'overdue', 'disbursed'])
            ->whereHas('schedules')
            ->with(['schedules' => fn ($query) => $query->orderBy('installment_number')])
            ->chunkById(50, function ($loans) {
                foreach ($loans as $loan) {
                    $this->sync($loan);
                }
            });
    }

    public function syncAllLoans(): int
    {
        $synced = 0;

        Loan::query()
            ->whereIn('status', ['active', 'overdue', 'disbursed'])
            ->whereHas('schedules')
            ->with(['schedules' => fn ($query) => $query->orderBy('installment_number')])
            ->chunkById(50, function ($loans) use (&$synced) {
                foreach ($loans as $loan) {
                    $this->sync($loan);
                    $synced++;
                }
            });

        return $synced;
    }

    private function resetLoanArrears(Loan $loan): void
    {
        $loan->overdue_days = 0;
        $loan->overdue_amount = 0;

        if ($loan->status === 'overdue') {
            $loan->status = 'active';
        }

        $loan->save();
    }

    private function isUnpaid(LoanSchedule $schedule): bool
    {
        return $schedule->remaining_total > 0.01;
    }

    /**
     * Live arrears for the installment currently accruing days.
     */
    private function liveArrearsDays(LoanSchedule $schedule, Carbon $today): int
    {
        if ($today->lte($schedule->due_date)) {
            return 0;
        }

        return (int) $schedule->due_date->diffInDays($today);
    }

    /**
     * Frozen arrears when a later installment has become due.
     */
    private function frozenArrearsDays(LoanSchedule $schedule, ?LoanSchedule $nextSchedule): int
    {
        if (!$nextSchedule) {
            return 0;
        }

        return max(0, (int) $schedule->due_date->diffInDays($nextSchedule->due_date));
    }

    private function applyScheduleStatus(LoanSchedule $schedule, Carbon $today): void
    {
        if (!$this->isUnpaid($schedule)) {
            $schedule->status = 'paid';
            $schedule->paid_date = $schedule->paid_date ?? now();
            $schedule->outstanding_amount = 0;
            $schedule->days_overdue = 0;

            return;
        }

        if ($schedule->paid_amount > 0) {
            $schedule->status = 'partial';
            $schedule->paid_date = null;

            return;
        }

        if ($schedule->days_overdue > 0 || $schedule->due_date->lt($today)) {
            $schedule->status = 'overdue';

            return;
        }

        $schedule->status = 'pending';
    }
}
