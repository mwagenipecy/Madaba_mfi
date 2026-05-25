<?php

namespace App\Jobs;

use App\Services\LoanArrearsCalculator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncLoanArrearsJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    public function __construct(
        public ?int $organizationId = null,
    ) {}

    public function handle(LoanArrearsCalculator $calculator): void
    {
        if ($this->organizationId) {
            $calculator->syncOrganizationLoans($this->organizationId);
            Log::info('Loan arrears sync completed.', ['organization_id' => $this->organizationId]);

            return;
        }

        $count = $calculator->syncAllLoans();
        Log::info('Loan arrears sync completed.', ['loans_synced' => $count]);
    }
}
