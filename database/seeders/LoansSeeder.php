<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\LoanDocument;
use App\Models\LoanTransaction;
use App\Models\Client;
use App\Models\LoanProduct;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\User;
use App\Models\Account;

class LoansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organization = Organization::first();
        $branch = Branch::first();
        $loanProduct = LoanProduct::first();
        $client = Client::first();
        $loanOfficer = User::where('role', 'loan_officer')->first();
        $adminUser = User::where('role', 'admin')->first();

        if (!$organization || !$branch || !$loanProduct || !$client || !$adminUser) {
            $this->command->warn('Missing required data for loans seeder. Please run other seeders first.');
            return;
        }

        // Get disbursement account (branch liability account)
        $disbursementAccount = Account::where('organization_id', $organization->id)
            ->where('branch_id', $branch->id)
            ->where('account_type_id', function($query) {
                $query->select('id')
                    ->from('account_types')
                    ->where('name', 'Liability');
            })
            ->first();

        $this->createSampleLoans($organization, $branch, $loanProduct, $client, $loanOfficer, $adminUser, $disbursementAccount);
    }

    private function createSampleLoans($organization, $branch, $loanProduct, $client, $loanOfficer, $adminUser, $disbursementAccount)
    {
        // Active Loan
        $activeLoan = Loan::create([
            'loan_number' => Loan::generateLoanNumber(),
            'client_id' => $client->id,
            'loan_product_id' => $loanProduct->id,
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'loan_officer_id' => $loanOfficer?->id,
            'loan_amount' => 5000000.00,
            'approved_amount' => 5000000.00,
            'interest_rate' => 18.00,
            'interest_calculation_method' => 'flat',
            'loan_tenure_months' => 12,
            'repayment_frequency' => 'monthly',
            'application_date' => now()->subDays(30),
            'approval_date' => now()->subDays(25),
            'disbursement_date' => now()->subDays(20),
            'first_payment_date' => now()->subDays(20),
            'maturity_date' => now()->addMonths(12)->subDays(20),
            'total_interest' => 900000.00,
            'total_amount' => 5900000.00,
            'monthly_payment' => 491666.67,
            'processing_fee' => 25000.00,
            'insurance_fee' => 50000.00,
            'status' => 'active',
            'approval_status' => 'approved',
            'approved_by' => $adminUser->id,
            'paid_amount' => 1475000.00,
            'outstanding_balance' => 4425000.00,
            'payments_made' => 3,
            'total_payments' => 12,
            'disbursement_account_id' => $disbursementAccount?->id,
            'disbursement_reference' => 'DISB-' . date('Ymd') . '-001',
            'loan_purpose' => 'Business expansion and working capital',
            'requires_collateral' => true,
            'collateral_description' => 'Land title deed - Plot No. 123/456',
            'collateral_value' => 8000000.00,
            'collateral_location' => 'Dar es Salaam',
        ]);

        // Pending Loan
        $pendingLoan = Loan::create([
            'loan_number' => Loan::generateLoanNumber(),
            'client_id' => Client::skip(1)->first()?->id ?? $client->id,
            'loan_product_id' => $loanProduct->id,
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'loan_officer_id' => $loanOfficer?->id,
            'loan_amount' => 2000000.00,
            'interest_rate' => 15.00,
            'interest_calculation_method' => 'reducing',
            'loan_tenure_months' => 6,
            'repayment_frequency' => 'monthly',
            'application_date' => now()->subDays(5),
            'status' => 'pending',
            'approval_status' => 'pending',
            'processing_fee' => 10000.00,
            'loan_purpose' => 'Equipment purchase',
        ]);

        // Overdue Loan
        $overdueLoan = Loan::create([
            'loan_number' => Loan::generateLoanNumber(),
            'client_id' => Client::skip(2)->first()?->id ?? $client->id,
            'loan_product_id' => $loanProduct->id,
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'loan_officer_id' => $loanOfficer?->id,
            'loan_amount' => 8000000.00,
            'approved_amount' => 8000000.00,
            'interest_rate' => 20.00,
            'interest_calculation_method' => 'flat',
            'loan_tenure_months' => 24,
            'repayment_frequency' => 'monthly',
            'application_date' => now()->subDays(60),
            'approval_date' => now()->subDays(55),
            'disbursement_date' => now()->subDays(50),
            'first_payment_date' => now()->subDays(50),
            'maturity_date' => now()->addMonths(24)->subDays(50),
            'total_interest' => 3200000.00,
            'total_amount' => 11200000.00,
            'monthly_payment' => 466666.67,
            'processing_fee' => 40000.00,
            'insurance_fee' => 80000.00,
            'status' => 'overdue',
            'approval_status' => 'approved',
            'approved_by' => $adminUser->id,
            'paid_amount' => 933333.34,
            'outstanding_balance' => 10266666.66,
            'overdue_amount' => 466666.67,
            'overdue_days' => 15,
            'payments_made' => 2,
            'total_payments' => 24,
            'disbursement_account_id' => $disbursementAccount?->id,
            'disbursement_reference' => 'DISB-' . date('Ymd') . '-002',
            'loan_purpose' => 'Real estate development',
            'requires_collateral' => true,
            'collateral_description' => 'Commercial building - 3 floors',
            'collateral_value' => 15000000.00,
            'collateral_location' => 'Arusha',
        ]);

        // Create schedules for active and overdue loans
        $activeLoan->calculateLoanSchedule();
        $overdueLoan->calculateLoanSchedule();

        $this->command->info('Created 3 sample loans with different statuses.');
    }
}
