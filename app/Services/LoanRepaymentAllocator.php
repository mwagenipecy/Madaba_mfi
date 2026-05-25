<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanSchedule;
use Carbon\Carbon;

class LoanRepaymentAllocator
{
    /**
     * Allocate a payment across loan schedules.
     *
     * Due/overdue installments: interest first, then principal.
     * Future installments (early payment): principal first, then interest.
     */
    public function allocate(Loan $loan, float $amount): array
    {
        $remaining = round($amount, 2);
        $allocations = [];
        $totalInterest = 0.0;
        $totalPrincipal = 0.0;

        $schedules = $loan->schedules()
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->orderBy('installment_number')
            ->get();

        foreach ($schedules as $schedule) {
            $schedule->refreshStatus();
            $schedule->save();
        }

        $schedules = $loan->schedules()
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->orderBy('installment_number')
            ->get();

        foreach ($schedules as $schedule) {
            if ($remaining <= 0) {
                break;
            }

            $scheduleRemaining = $schedule->remaining_total;
            if ($scheduleRemaining <= 0) {
                continue;
            }

            $isDueReached = $schedule->due_date->lte(Carbon::today());
            $slice = $this->allocateToSchedule($schedule, $remaining, $isDueReached);

            if ($slice['total'] <= 0) {
                continue;
            }

            $schedule->applyPayment($slice['principal'], $slice['interest']);

            $allocations[] = [
                'schedule_id' => $schedule->id,
                'installment_number' => $schedule->installment_number,
                'due_date' => $schedule->due_date->format('Y-m-d'),
                'allocation_mode' => $isDueReached ? 'due_interest_first' : 'early_principal_first',
                'principal' => $slice['principal'],
                'interest' => $slice['interest'],
                'total' => $slice['total'],
                'status' => $schedule->status,
            ];

            $totalPrincipal += $slice['principal'];
            $totalInterest += $slice['interest'];
            $remaining = round($remaining - $slice['total'], 2);
        }

        return [
            'allocations' => $allocations,
            'total_principal' => round($totalPrincipal, 2),
            'total_interest' => round($totalInterest, 2),
            'total_applied' => round($totalPrincipal + $totalInterest, 2),
            'unallocated' => max(0, $remaining),
        ];
    }

    /**
     * @return array{principal: float, interest: float, total: float}
     */
    private function allocateToSchedule(LoanSchedule $schedule, float $amount, bool $interestFirst): array
    {
        $remaining = round($amount, 2);
        $toInterest = 0.0;
        $toPrincipal = 0.0;

        $remInterest = $schedule->remaining_interest;
        $remPrincipal = $schedule->remaining_principal;

        if ($interestFirst) {
            $toInterest = min($remaining, $remInterest);
            $remaining = round($remaining - $toInterest, 2);
            $toPrincipal = min($remaining, $remPrincipal);
        } else {
            $toPrincipal = min($remaining, $remPrincipal);
            $remaining = round($remaining - $toPrincipal, 2);
            $toInterest = min($remaining, $remInterest);
        }

        return [
            'principal' => round($toPrincipal, 2),
            'interest' => round($toInterest, 2),
            'total' => round($toPrincipal + $toInterest, 2),
        ];
    }

    public function syncLoanTotals(Loan $loan): void
    {
        $loan->refresh();

        $paidFromSchedules = $loan->schedules()->sum('paid_amount');
        $loan->paid_amount = $paidFromSchedules;
        $loan->payments_made = $loan->transactions()
            ->whereIn('transaction_type', ['principal_payment', 'interest_payment'])
            ->where('status', 'completed')
            ->count();

        $outstandingFromSchedules = $loan->schedules()
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->sum('outstanding_amount');

        $loan->outstanding_balance = $outstandingFromSchedules;

        if ($outstandingFromSchedules <= 0.01) {
            $loan->status = 'completed';
            $loan->closure_date = $loan->closure_date ?? now();
            $loan->closed_by = $loan->closed_by ?? auth()->id();
            $loan->overdue_days = 0;
            $loan->overdue_amount = 0;
        }

        $loan->save();

        if ($outstandingFromSchedules > 0.01) {
            app(LoanArrearsCalculator::class)->sync($loan);
        }
    }
}
