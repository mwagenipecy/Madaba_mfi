<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\LoanSchedule;
use App\Models\LoanTransaction;
use App\Models\RepaymentRecord;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ClientScoringService
{
    public const MIN_PASSING_SCORE = 50;

    /** @var array<string, array{min: int, label: string, max_loan_pct: int}> */
    public const BANDS = [
        'excellent' => ['min' => 80, 'label' => 'Excellent', 'max_loan_pct' => 100],
        'good' => ['min' => 65, 'label' => 'Good', 'max_loan_pct' => 85],
        'fair' => ['min' => 50, 'label' => 'Fair', 'max_loan_pct' => 65],
        'poor' => ['min' => 35, 'label' => 'Poor', 'max_loan_pct' => 40],
        'critical' => ['min' => 0, 'label' => 'Critical', 'max_loan_pct' => 0],
    ];

    /**
     * @return array{
     *     score: int,
     *     band: string,
     *     band_label: string,
     *     passes: bool,
     *     recommended_max_loan: ?float,
     *     eligibility: array,
     *     history: array,
     *     profile: array,
     *     factors: array<string, int>,
     *     flags: list<array{type: string, message: string}>,
     *     reasons: list<string>,
     *     scored_at: string
     * }
     */
    public function score(
        Client $client,
        ?LoanProduct $product = null,
        ?float $requestedAmount = null,
        ?array $loanTerms = null
    ): array {
        $loans = $client->loans()->get();

        return $this->computeScore($client, $loans, $product, $requestedAmount, $loanTerms);
    }

    /**
     * @return array<string, mixed>
     */
    public function computeScore(
        Client $client,
        Collection $loans,
        ?LoanProduct $product = null,
        ?float $requestedAmount = null,
        ?array $loanTerms = null
    ): array {
        $history = $this->analyzeLoanHistory($loans);
        $profile = $this->analyzeProfile($client);

        $factors = [
            'repayment_history' => $this->repaymentHistoryScore($history),
            'arrears' => $this->arrearsScore($history),
            'profile' => $this->profileScore($client, $profile),
            'portfolio' => $this->portfolioScore($history),
        ];

        $score = (int) round(
            ($factors['repayment_history'] * 0.40) +
            ($factors['arrears'] * 0.25) +
            ($factors['profile'] * 0.20) +
            ($factors['portfolio'] * 0.15)
        );

        $score = max(0, min(100, $score));
        $band = $this->resolveBand($score);
        $flags = $this->buildFlags($client, $history, $profile);
        $eligibility = $this->checkEligibility($client, $product, $score, $requestedAmount, $history, $profile);
        $generalMax = $this->generalRecommendedMaxLoan($score, $client, $history);
        $productMax = $product ? $this->recommendedMaxLoanForProduct($product, $score, $client, $history) : null;
        $recommendedMax = $productMax !== null ? min($productMax, $generalMax) : $generalMax;

        $amountAssessment = null;
        if ($requestedAmount !== null && $requestedAmount > 0) {
            $amountAssessment = $this->assessRequestedAmount(
                $client,
                $score,
                $history,
                $requestedAmount,
                $generalMax,
                $productMax,
                $loanTerms
            );
        }

        return [
            'score' => $score,
            'band' => $band,
            'band_label' => self::BANDS[$band]['label'],
            'passes' => $eligibility['passes'],
            'recommended_max_loan' => $recommendedMax,
            'general_max_loan' => $generalMax,
            'product_max_loan' => $productMax,
            'amount_assessment' => $amountAssessment,
            'eligibility' => $eligibility,
            'history' => $history,
            'profile' => $profile,
            'factors' => $factors,
            'flags' => $flags,
            'reasons' => $this->buildReasons($score, $history, $profile, $flags, $eligibility),
            'scored_at' => now()->toIso8601String(),
        ];
    }

    public static function scoreColor(int $score): string
    {
        if ($score >= 80) {
            return '#16a34a';
        }
        if ($score >= 65) {
            return '#22c55e';
        }
        if ($score >= 50) {
            return '#eab308';
        }
        if ($score >= 35) {
            return '#f97316';
        }

        return '#dc2626';
    }

    /**
     * Monthly payment totals for the current calendar year.
     *
     * @return array{year: int, months: list<array{month: int, label: string, amount: float}>, total: float}
     */
    public function buildPaymentTrend(Client $client): array
    {
        $year = (int) now()->year;
        $monthly = array_fill(1, 12, 0.0);

        RepaymentRecord::query()
            ->where('client_id', $client->id)
            ->whereYear('payment_date', $year)
            ->get(['payment_date', 'amount'])
            ->each(function (RepaymentRecord $record) use (&$monthly) {
                $monthly[(int) $record->payment_date->format('n')] += (float) $record->amount;
            });

        if (array_sum($monthly) <= 0) {
            LoanTransaction::query()
                ->whereHas('loan', fn ($query) => $query->where('client_id', $client->id))
                ->whereIn('transaction_type', ['principal_payment', 'interest_payment'])
                ->where('status', 'completed')
                ->whereYear('transaction_date', $year)
                ->get(['transaction_date', 'amount'])
                ->each(function (LoanTransaction $transaction) use (&$monthly) {
                    $monthly[(int) $transaction->transaction_date->format('n')] += (float) $transaction->amount;
                });
        }

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = [
                'month' => $m,
                'label' => Carbon::create($year, $m, 1)->format('M'),
                'label_full' => Carbon::create($year, $m, 1)->format('F'),
                'amount' => round($monthly[$m], 2),
            ];
        }

        return [
            'year' => $year,
            'months' => $months,
            'total' => round(array_sum($monthly), 2),
        ];
    }

    /**
     * Monthly score trend for the current calendar year.
     *
     * @return array{year: int, months: list<array<string, mixed>>, trend_direction: string, trend_delta: int}
     */
    public function buildScoreTrend(Client $client): array
    {
        $year = (int) now()->year;
        $months = [];
        $previousScore = null;

        for ($m = 1; $m <= 12; $m++) {
            $asOf = Carbon::create($year, $m, 1)->endOfMonth()->startOfDay();
            $label = Carbon::create($year, $m, 1)->format('M');

            if ($asOf->isFuture()) {
                $months[] = [
                    'month' => $m,
                    'label' => $label,
                    'label_full' => Carbon::create($year, $m, 1)->format('F Y'),
                    'score' => null,
                    'band' => null,
                    'color' => '#d1d5db',
                    'change' => null,
                ];
                continue;
            }

            $snapshotLoans = $this->loansSnapshotAt($client, $asOf);
            $result = $this->computeScore($client, $snapshotLoans);
            $change = $previousScore !== null ? $result['score'] - $previousScore : null;
            $previousScore = $result['score'];

            $months[] = [
                'month' => $m,
                'label' => $label,
                'label_full' => Carbon::create($year, $m, 1)->format('F Y'),
                'score' => $result['score'],
                'band' => $result['band'],
                'band_label' => $result['band_label'],
                'color' => self::scoreColor($result['score']),
                'change' => $change,
            ];
        }

        $scoredMonths = collect($months)->filter(fn (array $row) => $row['score'] !== null)->values();
        $firstScore = $scoredMonths->first()['score'] ?? 0;
        $lastScore = $scoredMonths->last()['score'] ?? 0;
        $delta = $lastScore - $firstScore;

        return [
            'year' => $year,
            'months' => $months,
            'trend_direction' => $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'stable'),
            'trend_delta' => $delta,
        ];
    }

    /**
     * Build loan collection as it would have looked at a historical date.
     */
    private function loansSnapshotAt(Client $client, Carbon $asOf): Collection
    {
        $client->loadMissing(['loans.schedules']);

        $calculator = app(LoanArrearsCalculator::class);

        return $client->loans
            ->filter(function (Loan $loan) use ($asOf) {
                if (!$loan->application_date || $loan->application_date->gt($asOf)) {
                    return false;
                }

                if ($loan->disbursement_date && $loan->disbursement_date->gt($asOf)) {
                    return false;
                }

                return true;
            })
            ->map(function (Loan $loan) use ($asOf, $calculator) {
                $snapshot = clone $loan;

                if ($loan->write_off_date && $loan->write_off_date->lte($asOf)) {
                    $snapshot->status = 'written_off';
                    $snapshot->overdue_days = (int) ($loan->overdue_days ?? 0);

                    return $snapshot;
                }

                if ($loan->closure_date && $loan->closure_date->lte($asOf)) {
                    $snapshot->status = in_array($loan->status, ['completed', 'closed'], true) ? $loan->status : 'completed';
                    $snapshot->overdue_days = (int) ($loan->overdue_days ?? 0);

                    return $snapshot;
                }

                if ($loan->schedules->isNotEmpty()) {
                    $virtualSchedules = $loan->schedules->map(
                        fn (LoanSchedule $schedule) => $this->scheduleAsOf($schedule, $asOf)
                    );
                    $totals = $calculator->calculate($virtualSchedules, $asOf);
                    $snapshot->overdue_days = $totals['total_arrears_days'];
                    $snapshot->overdue_amount = $totals['overdue_amount'];
                    $snapshot->status = $totals['total_arrears_days'] > 0 ? 'overdue' : 'active';
                }

                return $snapshot;
            })
            ->values();
    }

    private function scheduleAsOf(LoanSchedule $schedule, Carbon $asOf): LoanSchedule
    {
        $clone = clone $schedule;

        if ($schedule->paid_date && $schedule->paid_date->lte($asOf)) {
            return $clone;
        }

        $clone->paid_amount = 0;
        $clone->paid_principal_amount = 0;
        $clone->paid_interest_amount = 0;
        $clone->paid_date = null;
        $clone->status = 'pending';
        $clone->days_overdue = 0;

        return $clone;
    }

    /**
     * @return array{
     *     total_loans: int,
     *     completed_loans: int,
     *     active_loans: int,
     *     overdue_loans: int,
     *     written_off_loans: int,
     *     rejected_loans: int,
     *     total_borrowed: float,
     *     total_repaid: float,
     *     current_outstanding: float,
     *     max_overdue_days: int,
     *     avg_overdue_days: float,
     *     on_time_completion_rate: ?float,
     *     is_first_time_borrower: bool
     * }
     */
    private function analyzeLoanHistory(Collection $loans): array
    {
        $completedStatuses = ['completed', 'closed'];
        $activeStatuses = ['active', 'overdue', 'disbursed'];
        $terminalBadStatuses = ['written_off', 'cancelled'];

        $completed = $loans->whereIn('status', $completedStatuses);
        $active = $loans->whereIn('status', $activeStatuses);
        $overdue = $loans->where('status', 'overdue');
        $writtenOff = $loans->where('status', 'written_off');
        $rejected = $loans->where('status', 'rejected');

        $overdueDays = $loans->pluck('overdue_days')->filter(fn ($days) => $days > 0);

        $onTimeRate = null;
        if ($completed->count() > 0) {
            $onTimeCompleted = $completed->filter(fn (Loan $loan) => ($loan->overdue_days ?? 0) === 0)->count();
            $onTimeRate = round(($onTimeCompleted / $completed->count()) * 100, 1);
        }

        return [
            'total_loans' => $loans->count(),
            'completed_loans' => $completed->count(),
            'active_loans' => $active->count(),
            'overdue_loans' => $overdue->count(),
            'written_off_loans' => $writtenOff->count(),
            'rejected_loans' => $rejected->count(),
            'total_borrowed' => round((float) $loans->sum('loan_amount'), 2),
            'total_repaid' => round((float) $loans->sum('paid_amount'), 2),
            'current_outstanding' => round((float) $active->sum('outstanding_balance'), 2),
            'max_overdue_days' => (int) $overdueDays->max(),
            'avg_overdue_days' => round((float) $overdueDays->avg(), 1),
            'on_time_completion_rate' => $onTimeRate,
            'is_first_time_borrower' => $loans->count() === 0,
        ];
    }

    /**
     * @return array{
     *     age: ?int,
     *     kyc_status: ?string,
     *     client_status: string,
     *     monthly_income: float,
     *     years_in_business: ?int
     * }
     */
    private function analyzeProfile(Client $client): array
    {
        $age = null;
        if ($client->date_of_birth) {
            $age = (int) $client->date_of_birth->diffInYears(Carbon::today());
        }

        $income = (float) ($client->monthly_income ?? 0);
        if ($income <= 0 && $client->annual_turnover) {
            $income = round((float) $client->annual_turnover / 12, 2);
        }

        return [
            'age' => $age,
            'kyc_status' => $client->kyc_status,
            'client_status' => $client->status,
            'monthly_income' => $income,
            'years_in_business' => $client->years_in_business,
        ];
    }

    private function repaymentHistoryScore(array $history): int
    {
        if ($history['is_first_time_borrower']) {
            return 70;
        }

        if ($history['written_off_loans'] > 0) {
            return max(10, 30 - ($history['written_off_loans'] * 15));
        }

        $onTimeRate = $history['on_time_completion_rate'];

        if ($onTimeRate === null) {
            return $history['active_loans'] > 0 ? 60 : 55;
        }

        if ($onTimeRate >= 90) {
            return 95;
        }

        if ($onTimeRate >= 75) {
            return 80;
        }

        if ($onTimeRate >= 50) {
            return 60;
        }

        return 35;
    }

    private function arrearsScore(array $history): int
    {
        if ($history['is_first_time_borrower']) {
            return 75;
        }

        if ($history['overdue_loans'] === 0 && $history['max_overdue_days'] === 0) {
            return 95;
        }

        if ($history['overdue_loans'] > 0) {
            $penalty = min(50, ($history['max_overdue_days'] * 2) + ($history['overdue_loans'] * 10));

            return max(5, 70 - $penalty);
        }

        if ($history['max_overdue_days'] > 0) {
            return max(20, 80 - min(60, $history['max_overdue_days'] * 2));
        }

        return 85;
    }

    private function profileScore(Client $client, array $profile): int
    {
        $score = 60;

        if ($client->status === 'blacklisted') {
            return 0;
        }

        if (in_array($client->status, ['suspended', 'inactive'], true)) {
            $score -= 25;
        }

        if ($profile['kyc_status'] === 'verified') {
            $score += 20;
        } elseif ($profile['kyc_status'] === 'pending') {
            $score += 5;
        } elseif ($profile['kyc_status'] === 'rejected') {
            $score -= 30;
        }

        if ($profile['monthly_income'] >= 500000) {
            $score += 10;
        } elseif ($profile['monthly_income'] >= 200000) {
            $score += 5;
        } elseif ($profile['monthly_income'] > 0) {
            $score += 2;
        }

        if ($client->client_type !== 'individual' && ($profile['years_in_business'] ?? 0) >= 2) {
            $score += 8;
        }

        return max(0, min(100, $score));
    }

    private function portfolioScore(array $history): int
    {
        if ($history['is_first_time_borrower']) {
            return 65;
        }

        $score = 70;

        if ($history['completed_loans'] > 0) {
            $score += min(20, $history['completed_loans'] * 5);
        }

        if ($history['rejected_loans'] > 0) {
            $score -= min(20, $history['rejected_loans'] * 8);
        }

        if ($history['active_loans'] > 2) {
            $score -= min(15, ($history['active_loans'] - 2) * 5);
        }

        if ($history['total_borrowed'] > 0) {
            $repaymentRatio = $history['total_repaid'] / $history['total_borrowed'];
            if ($repaymentRatio >= 0.9) {
                $score += 10;
            } elseif ($repaymentRatio >= 0.7) {
                $score += 5;
            } elseif ($repaymentRatio < 0.4) {
                $score -= 15;
            }
        }

        return max(0, min(100, $score));
    }

    private function resolveBand(int $score): string
    {
        foreach (self::BANDS as $key => $band) {
            if ($score >= $band['min']) {
                return $key;
            }
        }

        return 'critical';
    }

    /**
     * @return list<array{type: string, message: string}>
     */
    private function buildFlags(Client $client, array $history, array $profile): array
    {
        $flags = [];

        if ($client->status === 'blacklisted') {
            $flags[] = ['type' => 'danger', 'message' => 'Client is blacklisted and cannot receive new loans.'];
        }

        if ($history['written_off_loans'] > 0) {
            $flags[] = ['type' => 'danger', 'message' => 'Client has ' . $history['written_off_loans'] . ' written-off loan(s).'];
        }

        if ($history['overdue_loans'] > 0) {
            $flags[] = ['type' => 'warning', 'message' => 'Client currently has ' . $history['overdue_loans'] . ' overdue loan(s).'];
        }

        if ($history['active_loans'] > 0 && $history['current_outstanding'] > 0) {
            $flags[] = ['type' => 'info', 'message' => 'Outstanding balance on active loans: TZS ' . number_format($history['current_outstanding'], 2) . '.'];
        }

        if ($profile['kyc_status'] !== 'verified') {
            $flags[] = ['type' => 'warning', 'message' => 'KYC is not verified (' . ($profile['kyc_status'] ?? 'unknown') . ').'];
        }

        if ($history['is_first_time_borrower']) {
            $flags[] = ['type' => 'info', 'message' => 'First-time borrower — score is based on profile data only.'];
        }

        if ($history['max_overdue_days'] >= 30) {
            $flags[] = ['type' => 'warning', 'message' => 'Historical max arrears days: ' . $history['max_overdue_days'] . '.'];
        }

        return $flags;
    }

    /**
     * @return list<string>
     */
    private function buildReasons(int $score, array $history, array $profile, array $flags, array $eligibility): array
    {
        $reasons = [];

        if ($history['is_first_time_borrower']) {
            $reasons[] = 'No prior loan history — baseline score applied.';
        } elseif ($history['on_time_completion_rate'] !== null) {
            $reasons[] = 'On-time completion rate: ' . $history['on_time_completion_rate'] . '%.';
        }

        if ($history['completed_loans'] > 0) {
            $reasons[] = $history['completed_loans'] . ' successfully completed loan(s).';
        }

        if ($profile['kyc_status'] === 'verified') {
            $reasons[] = 'KYC verified — profile score boosted.';
        }

        if ($score >= 80) {
            $reasons[] = 'Eligible for standard product limits.';
        } elseif ($score >= 50) {
            $reasons[] = 'Eligible with reduced exposure limits.';
        } else {
            $reasons[] = 'High risk — manual review strongly recommended.';
        }

        foreach ($eligibility['checks'] as $check) {
            if (!$check['passed'] && !empty($check['message'])) {
                $reasons[] = $check['message'];
            }
        }

        return $reasons;
    }

    /**
     * @return array{passes: bool, checks: list<array{key: string, label: string, passed: bool, message: ?string}>}
     */
    private function checkEligibility(
        Client $client,
        ?LoanProduct $product,
        int $score,
        ?float $requestedAmount,
        array $history,
        array $profile
    ): array {
        $checks = [];
        $passes = true;

        $checks[] = $blacklistCheck = [
            'key' => 'client_status',
            'label' => 'Client status',
            'passed' => $client->status !== 'blacklisted',
            'message' => $client->status === 'blacklisted' ? 'Blacklisted clients cannot apply for loans.' : null,
        ];
        $passes = $passes && $blacklistCheck['passed'];

        $checks[] = $writeOffCheck = [
            'key' => 'write_offs',
            'label' => 'Write-off history',
            'passed' => $history['written_off_loans'] === 0,
            'message' => $history['written_off_loans'] > 0 ? 'Client has prior write-offs.' : null,
        ];
        $passes = $passes && $writeOffCheck['passed'];

        $criteria = $product?->eligibility_criteria ?? [];
        $minScore = self::MIN_PASSING_SCORE;
        if (!empty($criteria['minimum_credit_score'])) {
            $minScore = (int) $criteria['minimum_credit_score'];
        } elseif (!empty($criteria['credit_score_required'])) {
            $minScore = self::MIN_PASSING_SCORE;
        }

        $checks[] = $scoreCheck = [
            'key' => 'credit_score',
            'label' => 'Credit score',
            'passed' => $score >= $minScore,
            'message' => $score >= $minScore ? null : "Score {$score} is below minimum required ({$minScore}).",
        ];
        $passes = $passes && $scoreCheck['passed'];

        if ($profile['age'] !== null && isset($criteria['minimum_age'])) {
            $passed = $profile['age'] >= (int) $criteria['minimum_age'];
            $checks[] = [
                'key' => 'minimum_age',
                'label' => 'Minimum age',
                'passed' => $passed,
                'message' => $passed ? null : "Client age ({$profile['age']}) is below minimum ({$criteria['minimum_age']}).",
            ];
            $passes = $passes && $passed;
        }

        if ($profile['age'] !== null && isset($criteria['maximum_age'])) {
            $passed = $profile['age'] <= (int) $criteria['maximum_age'];
            $checks[] = [
                'key' => 'maximum_age',
                'label' => 'Maximum age',
                'passed' => $passed,
                'message' => $passed ? null : "Client age ({$profile['age']}) exceeds maximum ({$criteria['maximum_age']}).",
            ];
            $passes = $passes && $passed;
        }

        if (isset($criteria['minimum_income']) && $criteria['minimum_income'] > 0) {
            $passed = $profile['monthly_income'] >= (float) $criteria['minimum_income'];
            $checks[] = [
                'key' => 'minimum_income',
                'label' => 'Minimum income',
                'passed' => $passed,
                'message' => $passed ? null : 'Monthly income is below product minimum.',
            ];
            $passes = $passes && $passed;
        }

        if (!empty($criteria['employment_verification'])) {
            $passed = $profile['kyc_status'] === 'verified';
            $checks[] = [
                'key' => 'kyc_verified',
                'label' => 'KYC verification',
                'passed' => $passed,
                'message' => $passed ? null : 'Product requires verified KYC.',
            ];
            $passes = $passes && $passed;
        }

        if ($product && $requestedAmount !== null && $requestedAmount > 0) {
            $generalMax = $this->generalRecommendedMaxLoan($score, $client, $history);
            $recommendedMax = min($this->recommendedMaxLoanForProduct($product, $score, $client, $history), $generalMax);
            $passed = $requestedAmount <= $recommendedMax;
            $checks[] = [
                'key' => 'recommended_limit',
                'label' => 'Recommended loan limit',
                'passed' => $passed,
                'message' => $passed ? null : 'Requested amount exceeds recommended limit of TZS ' . number_format($recommendedMax, 2) . '.',
            ];
            $passes = $passes && $passed;
        } elseif ($requestedAmount !== null && $requestedAmount > 0) {
            $generalMax = $this->generalRecommendedMaxLoan($score, $client, $history);
            $passed = $requestedAmount <= $generalMax;
            $checks[] = [
                'key' => 'recommended_limit',
                'label' => 'Affordability (score & history)',
                'passed' => $passed,
                'message' => $passed
                    ? null
                    : 'Requested amount exceeds estimated limit of TZS ' . number_format($generalMax, 2) . ' based on income and repayment history.',
            ];
            $passes = $passes && $passed;
        }

        return [
            'passes' => $passes,
            'checks' => $checks,
        ];
    }

    private function recommendedMaxLoan(?LoanProduct $product, int $score, Client $client, array $history): ?float
    {
        $generalMax = $this->generalRecommendedMaxLoan($score, $client, $history);

        if (!$product) {
            return $generalMax;
        }

        return min($this->recommendedMaxLoanForProduct($product, $score, $client, $history), $generalMax);
    }

    private function generalRecommendedMaxLoan(int $score, Client $client, array $history): float
    {
        if ($client->status === 'blacklisted' || $history['written_off_loans'] > 0) {
            return 0.0;
        }

        $band = $this->resolveBand($score);
        $pct = self::BANDS[$band]['max_loan_pct'] / 100;

        if ($pct <= 0) {
            return 0.0;
        }

        $income = $this->clientMonthlyIncome($client);
        $base = 0.0;

        if ($income > 0) {
            $multiplier = $score >= 80 ? 6 : ($score >= 65 ? 4 : ($score >= 50 ? 3 : 2));
            $base = $income * $multiplier;
        } elseif ($history['total_loans'] > 0) {
            $avgLoan = $history['total_borrowed'] / $history['total_loans'];
            $base = $avgLoan * ($score >= 65 ? 1.5 : 1.0);
        } else {
            $base = 500000 * ($score >= 50 ? 1 : 0.5);
        }

        $limit = $base * $pct;

        if ($history['overdue_loans'] > 0) {
            $limit *= 0.5;
        }

        if ($history['current_outstanding'] > 0) {
            $limit = max(0, $limit - ($history['current_outstanding'] * 0.5));
        }

        return round(max(0, $limit), 2);
    }

    private function recommendedMaxLoanForProduct(LoanProduct $product, int $score, Client $client, array $history): float
    {
        $band = $this->resolveBand($score);
        $pct = self::BANDS[$band]['max_loan_pct'] / 100;

        if ($pct <= 0) {
            return 0.0;
        }

        $productMax = (float) $product->max_amount;
        $limit = $productMax * $pct;

        if ($client->status === 'blacklisted' || $history['written_off_loans'] > 0) {
            return 0.0;
        }

        if ($history['overdue_loans'] > 0) {
            $limit *= 0.5;
        }

        $income = $this->clientMonthlyIncome($client);

        if ($income > 0) {
            $incomeCap = $income * ($score >= 80 ? 6 : ($score >= 65 ? 4 : ($score >= 50 ? 3 : 2)));
            $limit = min($limit, $incomeCap);
        }

        return round(max(0, min($limit, $productMax)), 2);
    }

    private function clientMonthlyIncome(Client $client): float
    {
        $income = (float) ($client->monthly_income ?? 0);
        if ($income <= 0 && $client->annual_turnover) {
            $income = round((float) $client->annual_turnover / 12, 2);
        }

        return $income;
    }

    /**
     * @return array<string, mixed>
     */
    private function assessRequestedAmount(
        Client $client,
        int $score,
        array $history,
        float $requestedAmount,
        float $generalMax,
        ?float $productMax,
        ?array $loanTerms = null
    ): array {
        $effectiveMax = $productMax !== null ? min($productMax, $generalMax) : $generalMax;
        $affordable = $requestedAmount <= $effectiveMax;
        $income = $this->clientMonthlyIncome($client);

        $tenureMonths = max(1, (float) ($loanTerms['tenure_months'] ?? 12));
        $interestRate = max(0, (float) ($loanTerms['interest_rate'] ?? 0));
        $frequency = $loanTerms['repayment_frequency'] ?? 'monthly';
        $calcMethod = $loanTerms['interest_calculation_method'] ?? 'flat';

        $estimatedMonthly = $this->estimateMonthlyPayment(
            $requestedAmount,
            $interestRate,
            $tenureMonths,
            $frequency,
            $calcMethod
        );
        $maxMonthlyPct = $score >= 80 ? 0.35 : ($score >= 65 ? 0.30 : ($score >= 50 ? 0.25 : 0.15));
        $maxMonthlyPayment = $income > 0 ? round($income * $maxMonthlyPct, 2) : 0.0;

        $repaymentOutlook = 'good';
        if (!$affordable || $score < self::MIN_PASSING_SCORE) {
            $repaymentOutlook = 'poor';
        } elseif ($history['overdue_loans'] > 0 || ($income > 0 && $estimatedMonthly > $maxMonthlyPayment)) {
            $repaymentOutlook = 'fair';
        } elseif ($score < 65) {
            $repaymentOutlook = 'fair';
        }

        $alerts = [];

        if (!$affordable) {
            $alerts[] = [
                'type' => 'danger',
                'message' => 'Requested TZS ' . number_format($requestedAmount, 2)
                    . ' exceeds the recommended limit of TZS ' . number_format($effectiveMax, 2)
                    . ' based on this client\'s score and loan history.',
            ];
        } else {
            $alerts[] = [
                'type' => 'info',
                'message' => 'Requested amount is within the recommended limit of TZS ' . number_format($effectiveMax, 2) . '.',
            ];
        }

        if ($history['overdue_loans'] > 0) {
            $alerts[] = [
                'type' => 'danger',
                'message' => 'Client has ' . $history['overdue_loans'] . ' overdue loan(s) with '
                    . $history['max_overdue_days'] . ' max arrears days — repayment risk is elevated.',
            ];
        }

        if ($history['written_off_loans'] > 0) {
            $alerts[] = [
                'type' => 'danger',
                'message' => 'Previous write-off(s) on record — not recommended to lend this amount.',
            ];
        }

        if ($history['on_time_completion_rate'] !== null && $history['on_time_completion_rate'] < 75) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'On-time completion rate is only ' . $history['on_time_completion_rate']
                    . '% — past payment behaviour suggests caution.',
            ];
        }

        if ($history['current_outstanding'] > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Client already owes TZS ' . number_format($history['current_outstanding'], 2)
                    . ' on open contracts — consider total exposure, not just this request.',
            ];
        }

        if ($income > 0 && $estimatedMonthly > $maxMonthlyPayment) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Estimated installment (~TZS ' . number_format($estimatedMonthly, 2)
                    . ') may exceed safe capacity (~TZS ' . number_format($maxMonthlyPayment, 2)
                    . ' based on ' . round($maxMonthlyPct * 100) . '% of monthly income).',
            ];
        } elseif ($history['is_first_time_borrower']) {
            $alerts[] = [
                'type' => 'info',
                'message' => 'First-time borrower — limit is based on profile and income only; no repayment track record yet.',
            ];
        }

        return [
            'requested_amount' => round($requestedAmount, 2),
            'recommended_max_loan' => $effectiveMax,
            'general_max_loan' => $generalMax,
            'product_max_loan' => $productMax,
            'affordable' => $affordable,
            'can_proceed' => $affordable && $score >= self::MIN_PASSING_SCORE && $history['written_off_loans'] === 0,
            'repayment_outlook' => $repaymentOutlook,
            'estimated_monthly_payment' => $estimatedMonthly,
            'max_monthly_payment' => $maxMonthlyPayment,
            'loan_tenure_months' => $tenureMonths,
            'interest_rate' => $interestRate,
            'repayment_frequency' => $frequency,
            'alerts' => $alerts,
        ];
    }

    private function estimateMonthlyPayment(
        float $amount,
        float $interestRate,
        float $tenureMonths,
        string $frequency = 'monthly',
        string $calcMethod = 'flat'
    ): float {
        if ($amount <= 0 || $tenureMonths <= 0) {
            return 0.0;
        }

        $installments = match ($frequency) {
            'daily' => max(1, (int) round($tenureMonths * 30)),
            'weekly' => max(1, (int) round($tenureMonths * 4)),
            'quarterly' => max(1, (int) round($tenureMonths / 3)),
            default => max(1, (int) round($tenureMonths)),
        };

        $rate = $interestRate / 100;

        if ($calcMethod === 'reducing') {
            $periodicRate = match ($frequency) {
                'daily', 'weekly' => $rate / $installments,
                'quarterly' => $rate / 4,
                default => $rate / 12,
            };

            if ($periodicRate > 0) {
                $payment = $amount * ($periodicRate * pow(1 + $periodicRate, $installments))
                    / (pow(1 + $periodicRate, $installments) - 1);

                return round($payment, 2);
            }
        }

        $totalInterest = match ($frequency) {
            'daily', 'weekly' => $amount * $rate,
            default => $amount * $rate * ($tenureMonths / 12),
        };

        return round(($amount + $totalInterest) / $installments, 2);
    }

    /**
     * Full scoring report for the dedicated Scoring page and PDF export.
     *
     * @return array<string, mixed>
     */
    public function report(Client $client): array
    {
        $client->load(['loans.loanProduct', 'loans.schedules', 'organization', 'branch', 'verifiedBy']);

        $scoreData = $this->score($client);
        $loans = $client->loans->sortByDesc('application_date')->values();

        $loanDetails = $loans->map(fn (Loan $loan) => $this->mapLoanDetail($loan))->all();
        $openLoans = collect($loanDetails)->where('is_open', true)->values()->all();
        $closedLoans = collect($loanDetails)->where('is_open', false)->values()->all();
        $previousCompleted = collect($closedLoans)->whereIn('status', ['completed', 'closed'])->values()->all();
        $previousOtherClosed = collect($closedLoans)->whereNotIn('status', ['completed', 'closed'])->values()->all();
        $negativeContributors = collect($loanDetails)
            ->filter(fn (array $loan) => $loan['score_impact'] < 0)
            ->sortBy('score_impact')
            ->values()
            ->all();

        $productsTaken = $loans
            ->filter(fn (Loan $loan) => $loan->loanProduct)
            ->groupBy('loan_product_id')
            ->map(function ($group) {
                $product = $group->first()->loanProduct;
                $amounts = $group->sum('loan_amount');

                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'times_taken' => $group->count(),
                    'total_amount' => round((float) $amounts, 2),
                    'last_taken' => $group->max('application_date')?->format('Y-m-d'),
                ];
            })
            ->values()
            ->all();

        return array_merge($scoreData, [
            'client' => $this->mapClientSummary($client),
            'kyc' => $this->mapKycSummary($client),
            'loans' => [
                'open' => $openLoans,
                'closed' => $closedLoans,
                'previous_completed' => $previousCompleted,
                'previous_other' => $previousOtherClosed,
                'all' => $loanDetails,
            ],
            'products_taken' => $productsTaken,
            'negative_contributors' => $negativeContributors,
            'score_explanation' => $this->buildScoreExplanation($scoreData, $negativeContributors, $client),
            'payment_trend' => $this->buildPaymentTrend($client),
            'score_trend' => $this->buildScoreTrend($client),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapClientSummary(Client $client): array
    {
        return [
            'id' => $client->id,
            'client_number' => $client->client_number,
            'display_name' => $client->display_name,
            'client_type' => $client->client_type,
            'phone_number' => $client->phone_number,
            'email' => $client->email,
            'status' => $client->status,
            'organization' => $client->organization?->name,
            'branch' => $client->branch?->name,
            'monthly_income' => (float) ($client->monthly_income ?? 0),
            'annual_turnover' => (float) ($client->annual_turnover ?? 0),
            'years_in_business' => $client->years_in_business,
            'occupation' => $client->occupation,
            'employer_name' => $client->employer_name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapKycSummary(Client $client): array
    {
        $rawDocuments = $client->kyc_documents ?? $client->documents ?? [];
        $documents = [];

        if (is_array($rawDocuments)) {
            foreach ($rawDocuments as $document) {
                if (!is_array($document)) {
                    continue;
                }
                $documents[] = [
                    'type' => $document['type'] ?? 'document',
                    'name' => $document['name'] ?? 'Unknown',
                    'status' => $document['status'] ?? 'pending',
                    'description' => $document['description'] ?? null,
                    'uploaded_at' => isset($document['uploaded_at'])
                        ? Carbon::parse($document['uploaded_at'])->format('Y-m-d')
                        : null,
                    'size_kb' => isset($document['size']) ? round($document['size'] / 1024, 1) : null,
                ];
            }
        }

        return [
            'status' => $client->kyc_status,
            'verification_date' => $client->kyc_verification_date?->format('Y-m-d'),
            'verified_by' => $client->verifiedBy
                ? trim($client->verifiedBy->first_name . ' ' . $client->verifiedBy->last_name)
                : null,
            'kyc_notes' => $client->kyc_notes,
            'national_id' => $client->national_id,
            'passport_number' => $client->passport_number,
            'date_of_birth' => $client->date_of_birth?->format('Y-m-d'),
            'gender' => $client->gender,
            'physical_address' => $client->physical_address,
            'city' => $client->city,
            'region' => $client->region,
            'business_registration_number' => $client->business_registration_number,
            'business_type' => $client->business_type,
            'document_count' => count($documents),
            'documents' => $documents,
            'verified' => $client->kyc_status === 'verified',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapLoanDetail(Loan $loan): array
    {
        $impact = $this->loanScoreImpact($loan);
        $isOpen = in_array($loan->status, ['active', 'overdue', 'disbursed', 'approved', 'assessed', 'under_review', 'pending'], true);

        return [
            'id' => $loan->id,
            'loan_number' => $loan->loan_number,
            'product_name' => $loan->loanProduct?->name ?? 'N/A',
            'product_code' => $loan->loanProduct?->code,
            'loan_amount' => round((float) $loan->loan_amount, 2),
            'outstanding_balance' => round((float) ($loan->outstanding_balance ?? 0), 2),
            'paid_amount' => round((float) ($loan->paid_amount ?? 0), 2),
            'overdue_days' => (int) ($loan->overdue_days ?? 0),
            'overdue_amount' => round((float) ($loan->overdue_amount ?? 0), 2),
            'status' => $loan->status,
            'is_open' => $isOpen,
            'contract_type' => $isOpen ? 'open' : 'closed',
            'application_date' => $loan->application_date?->format('Y-m-d'),
            'disbursement_date' => $loan->disbursement_date?->format('Y-m-d'),
            'closure_date' => $loan->closure_date?->format('Y-m-d'),
            'write_off_date' => $loan->write_off_date?->format('Y-m-d'),
            'maturity_date' => $loan->maturity_date?->format('Y-m-d'),
            'score_impact' => $impact['points'],
            'impact_reasons' => $impact['reasons'],
        ];
    }

    /**
     * @return array{points: int, reasons: list<string>}
     */
    private function loanScoreImpact(Loan $loan): array
    {
        $points = 0;
        $reasons = [];

        if ($loan->status === 'written_off') {
            $points -= 40;
            $reasons[] = 'Loan was written off — major negative impact on credit score.';
        }

        if ($loan->status === 'overdue') {
            $days = (int) ($loan->overdue_days ?? 0);
            $penalty = min(25, 10 + (int) floor($days / 7));
            $points -= $penalty;
            $reasons[] = "Currently overdue ({$days} arrears days, TZS " . number_format((float) ($loan->overdue_amount ?? 0), 2) . ' outstanding).';
        } elseif (($loan->overdue_days ?? 0) > 0 && in_array($loan->status, ['completed', 'closed', 'active'], true)) {
            $days = (int) $loan->overdue_days;
            $penalty = min(15, (int) floor($days / 10));
            if ($penalty > 0) {
                $points -= $penalty;
                $reasons[] = "Historical arrears of {$days} days on this contract.";
            }
        }

        if ($loan->status === 'rejected') {
            $points -= 8;
            $reasons[] = 'Loan application was rejected.';
        }

        if (in_array($loan->status, ['completed', 'closed'], true) && ($loan->overdue_days ?? 0) === 0) {
            $points += 5;
            $reasons[] = 'Completed on time — positive repayment behavior.';
        }

        if ($loan->status === 'active' && ($loan->overdue_days ?? 0) === 0 && ($loan->paid_amount ?? 0) > 0) {
            $points += 2;
            $reasons[] = 'Active contract with repayments and no current arrears.';
        }

        return ['points' => $points, 'reasons' => $reasons];
    }

    /**
     * @param  list<array<string, mixed>>  $negativeContributors
     * @return list<string>
     */
    private function buildScoreExplanation(array $scoreData, array $negativeContributors, Client $client): array
    {
        $explanations = [];

        if ($scoreData['score'] < self::MIN_PASSING_SCORE) {
            $explanations[] = 'Overall score is below the passing threshold (' . self::MIN_PASSING_SCORE . '/100), indicating elevated credit risk.';
        }

        foreach ($scoreData['factors'] as $factor => $value) {
            if ($value < 60) {
                $label = match ($factor) {
                    'repayment_history' => 'Repayment history',
                    'arrears' => 'Arrears record',
                    'profile' => 'Client profile & KYC',
                    'portfolio' => 'Portfolio behavior',
                    default => ucfirst(str_replace('_', ' ', $factor)),
                };
                $explanations[] = "{$label} scored {$value}/100 — this factor is pulling the overall score down.";
            }
        }

        if (count($negativeContributors) > 0) {
            $top = $negativeContributors[0];
            $explanations[] = 'Primary negative contributor: ' . $top['loan_number'] . ' (' . $top['product_name'] . ') — ' . implode(' ', $top['impact_reasons']);
        }

        if ($client->kyc_status !== 'verified') {
            $explanations[] = 'KYC status is "' . ($client->kyc_status ?? 'unknown') . '" — verified KYC would improve the profile factor.';
        }

        if ($scoreData['history']['is_first_time_borrower']) {
            $explanations[] = 'Limited data: client has no prior loan history, so the score relies heavily on profile information.';
        }

        if (empty($explanations)) {
            $explanations[] = 'Score is within acceptable range based on repayment history, profile, and current portfolio exposure.';
        }

        return $explanations;
    }
}
