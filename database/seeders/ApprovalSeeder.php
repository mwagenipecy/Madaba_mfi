<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Approval;
use App\Models\FundTransfer;
use App\Models\AccountRecharge;
use App\Models\Account;
use App\Models\User;
use App\Models\Organization;
use App\Models\Branch;
use Carbon\Carbon;

class ApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing approval data
        Approval::truncate();
        FundTransfer::truncate();
        AccountRecharge::truncate();

        $organization = Organization::first();
        if (!$organization) {
            $this->command->error('No organization found. Please run OrganizationSeeder first.');
            return;
        }

        $users = User::where('organization_id', $organization->id)->get();
        if ($users->isEmpty()) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        $branches = Branch::where('organization_id', $organization->id)->get();
        if ($branches->isEmpty()) {
            $this->command->error('No branches found. Please run BranchSeeder first.');
            return;
        }

        $accounts = Account::where('organization_id', $organization->id)->get();
        if ($accounts->isEmpty()) {
            $this->command->error('No accounts found. Please run AccountSeeder first.');
            return;
        }

        // Create fund transfers
        $this->createFundTransfers($organization, $users, $accounts);
        
        // Create account recharges
        $this->createAccountRecharges($organization, $users, $accounts, $branches);
        
        // Create loan approvals
        $this->createLoanApprovals($organization, $users);
    }

    private function createFundTransfers($organization, $users, $accounts)
    {
        $mainAccounts = $accounts->where('account_type_id', 1); // Asset accounts
        $liabilityAccounts = $accounts->where('account_type_id', 2); // Liability accounts

        $transferTypes = [
            'Inter-branch transfer',
            'Cash deposit to main account',
            'Fund allocation to branch',
            'Emergency fund transfer',
            'Regular operational transfer'
        ];

        for ($i = 1; $i <= 15; $i++) {
            $fromAccount = $mainAccounts->random();
            $toAccount = $liabilityAccounts->random();
            $requester = $users->random();
            $approver = $users->where('id', '!=', $requester->id)->random();

            $amount = rand(50000, 2000000); // TZS 50,000 to 2,000,000
            $status = $this->getRandomStatus();
            $createdAt = $this->getRandomDate();

            $fundTransfer = FundTransfer::create([
                'transfer_number' => 'FT-' . date('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'amount' => $amount,
                'currency' => 'TZS',
                'description' => $transferTypes[array_rand($transferTypes)],
                'status' => $status,
                'requested_by' => $requester->id,
                'approved_by' => $status === 'pending' ? null : $approver->id,
                'approved_at' => $status === 'pending' ? null : $createdAt->copy()->addHours(rand(1, 24)),
                'completed_at' => in_array($status, ['approved', 'completed']) ? $createdAt->copy()->addHours(rand(24, 48)) : null,
                'rejection_reason' => $status === 'rejected' ? 'Insufficient documentation provided' : null,
                'metadata' => [
                    'transfer_type' => 'inter_branch',
                    'priority' => rand(1, 5),
                    'notes' => 'Automated transfer request'
                ]
            ]);

            // Create approval record
            if ($status !== 'pending') {
                Approval::create([
                    'approval_number' => 'APP-FT-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'type' => 'fund_transfer',
                    'reference_type' => 'FundTransfer',
                    'reference_id' => $fundTransfer->id,
                    'requested_by' => $requester->id,
                    'approver_id' => $approver->id,
                    'status' => $status,
                    'description' => "Fund transfer approval: {$fundTransfer->transfer_number}",
                    'approval_notes' => $status === 'approved' ? 'Transfer approved after review' : 'Transfer rejected due to insufficient funds',
                    'approved_at' => $status === 'pending' ? null : $fundTransfer->approved_at,
                    'metadata' => [
                        'amount' => $amount,
                        'from_account' => $fromAccount->name,
                        'to_account' => $toAccount->name
                    ]
                ]);
            }
        }
    }

    private function createAccountRecharges($organization, $users, $accounts, $branches)
    {
        $mainAccounts = $accounts->where('account_type_id', 1)->whereNull('branch_id'); // Main organization accounts
        $branchAccounts = $accounts->where('account_type_id', 2)->whereNotNull('branch_id'); // Branch liability accounts

        $rechargeTypes = [
            'Monthly operational funding',
            'Emergency fund allocation',
            'Quarterly budget distribution',
            'Special project funding',
            'Year-end allocation'
        ];

        for ($i = 1; $i <= 12; $i++) {
            $mainAccount = $mainAccounts->random();
            $requester = $users->random();
            $approver = $users->where('id', '!=', $requester->id)->random();

            $amount = rand(100000, 5000000); // TZS 100,000 to 5,000,000
            $status = $this->getRandomStatus();
            $createdAt = $this->getRandomDate();

            // Create distribution plan for branch accounts
            $distributionPlan = [];
            $remainingAmount = $amount;
            $maxBranches = min($branches->count(), 4);
            $minBranches = min(2, $maxBranches);
            $selectedBranches = $branches->random(rand($minBranches, $maxBranches));

            foreach ($selectedBranches as $branch) {
                $branchAccount = $branchAccounts->where('branch_id', $branch->id)->first();
                if ($branchAccount && $remainingAmount > 0) {
                    $distributionAmount = min(rand(50000, $remainingAmount), $remainingAmount);
                    $distributionPlan[] = [
                        'account_id' => $branchAccount->id,
                        'amount' => $distributionAmount,
                        'branch_name' => $branch->name
                    ];
                    $remainingAmount -= $distributionAmount;
                }
            }

            $accountRecharge = AccountRecharge::create([
                'recharge_number' => 'RC-' . date('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'main_account_id' => $mainAccount->id,
                'recharge_amount' => $amount,
                'currency' => 'TZS',
                'description' => $rechargeTypes[array_rand($rechargeTypes)],
                'status' => $status,
                'requested_by' => $requester->id,
                'approved_by' => $status === 'pending' ? null : $approver->id,
                'approved_at' => $status === 'pending' ? null : $createdAt->copy()->addHours(rand(1, 24)),
                'completed_at' => in_array($status, ['approved', 'completed']) ? $createdAt->copy()->addHours(rand(24, 48)) : null,
                'rejection_reason' => $status === 'rejected' ? 'Budget constraints - request exceeds available funds' : null,
                'distribution_plan' => $distributionPlan,
                'metadata' => [
                    'recharge_type' => 'operational',
                    'priority' => rand(1, 5),
                    'quarter' => ceil($i / 3),
                    'notes' => 'Automated recharge request'
                ]
            ]);

            // Create approval record
            if ($status !== 'pending') {
                Approval::create([
                    'approval_number' => 'APP-RC-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'type' => 'account_recharge',
                    'reference_type' => 'AccountRecharge',
                    'reference_id' => $accountRecharge->id,
                    'requested_by' => $requester->id,
                    'approver_id' => $approver->id,
                    'status' => $status,
                    'description' => "Account recharge approval: {$accountRecharge->recharge_number}",
                    'approval_notes' => $status === 'approved' ? 'Recharge approved for operational needs' : 'Recharge rejected due to budget limitations',
                    'approved_at' => $status === 'pending' ? null : $accountRecharge->approved_at,
                    'metadata' => [
                        'amount' => $amount,
                        'main_account' => $mainAccount->name,
                        'distribution_count' => count($distributionPlan)
                    ]
                ]);
            }
        }
    }

    private function createLoanApprovals($organization, $users)
    {
        $loans = \App\Models\Loan::where('organization_id', $organization->id)->get();
        
        if ($loans->isEmpty()) {
            $this->command->warn('No loans found. Skipping loan approvals.');
            return;
        }

        $approvalTypes = [
            'loan_application',
            'loan_disbursement',
            'loan_restructure',
            'loan_write_off',
            'loan_top_up'
        ];

        foreach ($loans->take(10) as $index => $loan) {
            $requester = $users->random();
            $approver = $users->where('id', '!=', $requester->id)->random();
            $status = $this->getRandomStatus();
            $createdAt = $this->getRandomDate();

            $approvalType = $approvalTypes[array_rand($approvalTypes)];

            Approval::create([
                'approval_number' => 'APP-LN-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                'type' => 'other',
                'reference_type' => 'Loan',
                'reference_id' => $loan->id,
                'requested_by' => $requester->id,
                'approver_id' => $approver->id,
                'status' => $status,
                'description' => "Loan {$approvalType}: {$loan->loan_number}",
                'approval_notes' => $status === 'approved' ? 'Loan request approved after thorough review' : 'Loan request requires additional documentation',
                'approved_at' => $status === 'pending' ? null : $createdAt->copy()->addHours(rand(1, 48)),
                'metadata' => [
                    'loan_amount' => $loan->loan_amount,
                    'client_name' => $loan->client->display_name ?? 'N/A',
                    'approval_type' => $approvalType,
                    'priority' => rand(1, 5)
                ]
            ]);
        }
    }

    private function getRandomStatus(): string
    {
        $statuses = ['pending', 'approved', 'rejected'];
        $weights = [30, 50, 20]; // 30% pending, 50% approved, 20% rejected
        
        $random = rand(1, 100);
        $cumulative = 0;
        
        foreach ($statuses as $index => $status) {
            $cumulative += $weights[$index];
            if ($random <= $cumulative) {
                return $status;
            }
        }
        
        return 'pending';
    }

    private function getRandomDate(): Carbon
    {
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();
        
        $random = rand($startDate->timestamp, $endDate->timestamp);
        return Carbon::createFromTimestamp($random);
    }
}
