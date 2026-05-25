<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Models\Organization;
use App\Services\LoanArrearsCalculator;
use Illuminate\Console\Command;

class SyncLoanArrears extends Command
{
    protected $signature = 'loans:sync-arrears {--organization-id= : Sync arrears for a single organization only}';

    protected $description = 'Recalculate loan schedule arrears days and overdue totals';

    public function handle(LoanArrearsCalculator $calculator): int
    {
        $organizationId = $this->option('organization-id');

        if ($organizationId) {
            $this->syncOrganization((int) $organizationId, $calculator);

            return self::SUCCESS;
        }

        $organizations = Organization::query()->pluck('id');
        $this->info('Syncing loan arrears for ' . $organizations->count() . ' organization(s)...');

        foreach ($organizations as $orgId) {
            $this->syncOrganization((int) $orgId, $calculator);
        }

        $this->info('Loan arrears sync completed.');

        return self::SUCCESS;
    }

    private function syncOrganization(int $organizationId, LoanArrearsCalculator $calculator): void
    {
        $count = Loan::query()
            ->where('organization_id', $organizationId)
            ->whereIn('status', ['active', 'overdue', 'disbursed'])
            ->whereHas('schedules')
            ->count();

        if ($count === 0) {
            return;
        }

        $this->line("Organization {$organizationId}: syncing {$count} loan(s)...");

        $calculator->syncOrganizationLoans($organizationId);
    }
}
