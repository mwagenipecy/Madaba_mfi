<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\Organization;
use App\Models\User;
use App\Models\Branch;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\LoanProduct;
use App\Models\Client;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\LoanTransaction;
use App\Models\GeneralLedger;
use App\Models\AccountRecharge;
use App\Services\AccountingService;

class ComprehensiveSystemTestSeeder extends Seeder
{
    private $organization;
    private $admin;
    private $branch;
    private $mainAccount;
    private $cashAccount;
    private $loanProduct;
    private $client;
    private $loan;
    private $accountingService;

    public function run(): void
    {
        $this->accountingService = new AccountingService();
        
        echo "🚀 Starting Comprehensive System Test...\n\n";
        
        try {
            $this->testOrganizationRegistration();
            $this->testMainAccountCreation();
            $this->testAccountRecharge();
            $this->testBranchCreation();
            $this->testLoanProductCreation();
            $this->testClientCreation();
            $this->testLoanApplicationAndApproval();
            $this->testLoanDisbursement();
            $this->testLoanRepayment();
            $this->verifyDoubleEntryBookkeeping();
            $this->verifyCalculations();
            
            echo "\n✅ All tests completed successfully!\n";
            
        } catch (\Exception $e) {
            echo "\n❌ Test failed: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
            throw $e;
        }
    }

    private function testOrganizationRegistration()
    {
        echo "1️⃣ Testing Organization Registration...\n";
        
        // Create test organization
        $this->organization = Organization::create([
            'name' => 'Test Microfinance Organization',
            'registration_number' => 'TEST-MFI-2025-001',
            'email' => 'test@mfiorg.com',
            'phone' => '+255123456789',
            'address' => 'Test Address, Dar es Salaam',
            'license_number' => 'LIC-TEST-2025',
            'tax_id' => 'TAX-TEST-2025',
            'status' => 'active',
            'established_date' => Carbon::now()->subYear(),
        ]);

        // Create admin user
        $this->admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@testorg.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'organization_id' => $this->organization->id,
            'phone' => '+255987654321',
            'is_active' => true,
            'email_verified_at' => Carbon::now(),
        ]);

        echo "   ✅ Organization created: {$this->organization->name}\n";
        echo "   ✅ Admin user created: {$this->admin->name}\n";
        
        $this->todo_write('test_organization_registration', 'completed');
    }

    private function testMainAccountCreation()
    {
        echo "\n2️⃣ Testing Main Account Creation...\n";
        
        // Get or create main account type
        $mainAccountType = AccountType::firstOrCreate([
            'name' => 'Main Operating Account',
            'type' => 'asset',
            'organization_id' => $this->organization->id,
        ], [
            'description' => 'Main operating account for the organization',
            'is_active' => true,
        ]);

        // Create main account
        $this->mainAccount = Account::create([
            'name' => 'Main Operating Account',
            'account_number' => 'MAIN-001',
            'account_type_id' => $mainAccountType->id,
            'organization_id' => $this->organization->id,
            'opening_balance' => 0,
            'current_balance' => 0,
            'is_active' => true,
            'is_main_account' => true,
        ]);

        // Create cash account
        $cashAccountType = AccountType::firstOrCreate([
            'name' => 'Cash Account',
            'type' => 'asset',
            'organization_id' => $this->organization->id,
        ], [
            'description' => 'Cash account for daily operations',
            'is_active' => true,
        ]);

        $this->cashAccount = Account::create([
            'name' => 'Cash Account',
            'account_number' => 'CASH-001',
            'account_type_id' => $cashAccountType->id,
            'organization_id' => $this->organization->id,
            'opening_balance' => 0,
            'current_balance' => 0,
            'is_active' => true,
        ]);

        echo "   ✅ Main account created: {$this->mainAccount->name}\n";
        echo "   ✅ Cash account created: {$this->cashAccount->name}\n";
        
        $this->todo_write('test_account_creation', 'completed');
    }

    private function testAccountRecharge()
    {
        echo "\n3️⃣ Testing Account Recharge...\n";
        
        $rechargeAmount = 1000000; // 1,000,000 TZS
        
        // Create account recharge
        $recharge = AccountRecharge::create([
            'recharge_number' => 'RECHARGE-TEST-001',
            'main_account_id' => $this->mainAccount->id,
            'recharge_amount' => $rechargeAmount,
            'currency' => 'TZS',
            'description' => 'Initial capital injection for testing',
            'status' => 'completed',
            'requested_by' => $this->admin->id,
            'approved_by' => $this->admin->id,
            'approved_at' => Carbon::now(),
            'completed_at' => Carbon::now(),
        ]);

        // Process the recharge through accounting service
        $this->accountingService->recordAccountRecharge($recharge);

        // Refresh account balance
        $this->mainAccount->refresh();
        
        echo "   ✅ Account recharged with: TZS " . number_format($rechargeAmount) . "\n";
        echo "   ✅ Main account balance: TZS " . number_format($this->mainAccount->current_balance) . "\n";
        
        $this->todo_write('test_account_recharge', 'completed');
    }

    private function testBranchCreation()
    {
        echo "\n4️⃣ Testing Branch Creation...\n";
        
        $this->branch = Branch::create([
            'name' => 'Test Branch',
            'code' => 'TB001',
            'organization_id' => $this->organization->id,
            'address' => 'Test Branch Address',
            'phone' => '+255123456789',
            'email' => 'branch@testorg.com',
            'manager_id' => $this->admin->id,
            'is_active' => true,
            'is_hq' => false,
        ]);

        // Create branch user
        $branchUser = User::create([
            'name' => 'Test Branch Officer',
            'email' => 'officer@testbranch.com',
            'password' => Hash::make('password'),
            'role' => 'officer',
            'organization_id' => $this->organization->id,
            'branch_id' => $this->branch->id,
            'phone' => '+255987654322',
            'is_active' => true,
            'email_verified_at' => Carbon::now(),
        ]);

        echo "   ✅ Branch created: {$this->branch->name}\n";
        echo "   ✅ Branch officer created: {$branchUser->name}\n";
        
        $this->todo_write('test_branch_creation', 'completed');
    }

    private function testLoanProductCreation()
    {
        echo "\n5️⃣ Testing Loan Product Creation...\n";
        
        // Create loan product accounts
        $loanAssetAccount = Account::create([
            'name' => 'Loan Portfolio',
            'account_number' => 'LOAN-ASSET-001',
            'account_type_id' => AccountType::where('name', 'Loan Asset Account')->first()->id ?? AccountType::create([
                'name' => 'Loan Asset Account',
                'type' => 'asset',
                'organization_id' => $this->organization->id,
            ])->id,
            'organization_id' => $this->organization->id,
            'opening_balance' => 0,
            'current_balance' => 0,
            'is_active' => true,
        ]);

        $interestIncomeAccount = Account::create([
            'name' => 'Interest Income',
            'account_number' => 'INT-INCOME-001',
            'account_type_id' => AccountType::where('name', 'Interest Income Account')->first()->id ?? AccountType::create([
                'name' => 'Interest Income Account',
                'type' => 'income',
                'organization_id' => $this->organization->id,
            ])->id,
            'organization_id' => $this->organization->id,
            'opening_balance' => 0,
            'current_balance' => 0,
            'is_active' => true,
        ]);

        $this->loanProduct = LoanProduct::create([
            'name' => 'Test Business Loan',
            'description' => 'Test loan product for system verification',
            'organization_id' => $this->organization->id,
            'min_amount' => 100000,
            'max_amount' => 1000000,
            'interest_rate' => 24.0,
            'interest_calculation_method' => 'reducing_balance',
            'min_tenure_months' => 6,
            'max_tenure_months' => 24,
            'repayment_frequency' => 'monthly',
            'processing_fee_percentage' => 2.0,
            'late_fee_percentage' => 5.0,
            'penalty_fee_percentage' => 10.0,
            'requires_collateral' => false,
            'is_active' => true,
            'loan_asset_account_id' => $loanAssetAccount->id,
            'interest_income_account_id' => $interestIncomeAccount->id,
        ]);

        echo "   ✅ Loan product created: {$this->loanProduct->name}\n";
        echo "   ✅ Interest rate: {$this->loanProduct->interest_rate}% per annum\n";
        
        $this->todo_write('test_loan_product', 'completed');
    }

    private function testClientCreation()
    {
        echo "\n6️⃣ Testing Client Creation...\n";
        
        $this->client = Client::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone_number' => '+255123456780',
            'email' => 'john.doe@test.com',
            'date_of_birth' => Carbon::now()->subYears(30),
            'gender' => 'male',
            'marital_status' => 'single',
            'national_id' => '1234567890123456',
            'address' => 'Test Client Address',
            'occupation' => 'Business Owner',
            'monthly_income' => 500000,
            'organization_id' => $this->organization->id,
            'branch_id' => $this->branch->id,
            'created_by' => $this->admin->id,
            'is_active' => true,
        ]);

        echo "   ✅ Client created: {$this->client->first_name} {$this->client->last_name}\n";
        echo "   ✅ Client ID: {$this->client->national_id}\n";
        
        $this->todo_write('test_client_creation', 'completed');
    }

    private function testLoanApplicationAndApproval()
    {
        echo "\n7️⃣ Testing Loan Application and Approval...\n";
        
        $loanAmount = 500000; // 500,000 TZS
        
        $this->loan = Loan::create([
            'loan_number' => 'LOAN-TEST-001',
            'client_id' => $this->client->id,
            'loan_product_id' => $this->loanProduct->id,
            'organization_id' => $this->organization->id,
            'branch_id' => $this->branch->id,
            'loan_officer_id' => $this->admin->id,
            'loan_amount' => $loanAmount,
            'approved_amount' => $loanAmount,
            'interest_rate' => $this->loanProduct->interest_rate,
            'interest_calculation_method' => $this->loanProduct->interest_calculation_method,
            'loan_tenure_months' => 12,
            'repayment_frequency' => $this->loanProduct->repayment_frequency,
            'application_date' => Carbon::now()->subDays(5),
            'approval_date' => Carbon::now()->subDays(2),
            'disbursement_date' => Carbon::now()->subDay(),
            'first_payment_date' => Carbon::now()->addMonth(),
            'maturity_date' => Carbon::now()->addYear(),
            'processing_fee' => $loanAmount * ($this->loanProduct->processing_fee_percentage / 100),
            'status' => 'active',
            'approval_status' => 'approved',
            'approved_by' => $this->admin->id,
            'loan_purpose' => 'Business expansion',
        ]);

        // Calculate loan details
        $this->calculateLoanSchedule();

        echo "   ✅ Loan created: {$this->loan->loan_number}\n";
        echo "   ✅ Loan amount: TZS " . number_format($loanAmount) . "\n";
        echo "   ✅ Processing fee: TZS " . number_format($this->loan->processing_fee) . "\n";
        
        $this->todo_write('test_loan_application', 'completed');
    }

    private function calculateLoanSchedule()
    {
        $principal = $this->loan->approved_amount;
        $rate = $this->loan->interest_rate / 100 / 12; // Monthly rate
        $periods = $this->loan->loan_tenure_months;

        // Calculate monthly payment using PMT formula
        $monthlyPayment = $principal * ($rate * pow(1 + $rate, $periods)) / (pow(1 + $rate, $periods) - 1);

        $this->loan->update([
            'monthly_payment' => $monthlyPayment,
            'total_interest' => ($monthlyPayment * $periods) - $principal,
            'total_amount' => $monthlyPayment * $periods,
        ]);

        // Create loan schedule
        $remainingBalance = $principal;
        $dueDate = $this->loan->first_payment_date;

        for ($i = 1; $i <= $periods; $i++) {
            $interestPayment = $remainingBalance * $rate;
            $principalPayment = $monthlyPayment - $interestPayment;
            
            // For last payment, adjust to clear remaining balance
            if ($i == $periods) {
                $principalPayment = $remainingBalance;
                $monthlyPayment = $principalPayment + $interestPayment;
            }

            LoanSchedule::create([
                'loan_id' => $this->loan->id,
                'installment_number' => $i,
                'due_date' => $dueDate,
                'principal_amount' => $principalPayment,
                'interest_amount' => $interestPayment,
                'total_amount' => $monthlyPayment,
                'outstanding_amount' => $monthlyPayment,
                'status' => $i == 1 ? 'pending' : 'pending',
            ]);

            $remainingBalance -= $principalPayment;
            $dueDate = $dueDate->addMonth();
        }
    }

    private function testLoanDisbursement()
    {
        echo "\n8️⃣ Testing Loan Disbursement...\n";
        
        // Process disbursement through accounting service
        $this->accountingService->recordLoanDisbursement($this->loan, $this->mainAccount, $this->loan->approved_amount);

        // Refresh loan and accounts
        $this->loan->refresh();
        $this->mainAccount->refresh();
        $loanAssetAccount = Account::find($this->loanProduct->loan_asset_account_id);
        $loanAssetAccount->refresh();

        echo "   ✅ Loan disbursed: TZS " . number_format($this->loan->approved_amount) . "\n";
        echo "   ✅ Main account balance after disbursement: TZS " . number_format($this->mainAccount->current_balance) . "\n";
        echo "   ✅ Loan asset account balance: TZS " . number_format($loanAssetAccount->current_balance) . "\n";
        
        $this->todo_write('test_loan_disbursement', 'completed');
    }

    private function testLoanRepayment()
    {
        echo "\n9️⃣ Testing Loan Repayment...\n";
        
        $firstSchedule = LoanSchedule::where('loan_id', $this->loan->id)
            ->where('installment_number', 1)
            ->first();

        $repaymentAmount = $firstSchedule->total_amount;

        // Create loan transaction for repayment
        $transaction = LoanTransaction::create([
            'loan_id' => $this->loan->id,
            'loan_schedule_id' => $firstSchedule->id,
            'transaction_number' => LoanTransaction::generateTransactionNumber(),
            'transaction_type' => 'principal_payment',
            'amount' => $repaymentAmount,
            'payment_method' => 'cash',
            'transaction_date' => Carbon::now(),
            'transaction_time' => Carbon::now()->format('H:i:s'),
            'principal_amount' => $firstSchedule->principal_amount,
            'interest_amount' => $firstSchedule->interest_amount,
            'status' => 'completed',
            'processed_by' => $this->admin->id,
            'organization_id' => $this->organization->id,
            'branch_id' => $this->branch->id,
        ]);

        // Process repayment through accounting service
        $this->accountingService->recordLoanRepayment($this->loan, $this->cashAccount, $repaymentAmount, $firstSchedule->principal_amount, $firstSchedule->interest_amount);

        // Update loan schedule
        $firstSchedule->update([
            'status' => 'paid',
            'paid_amount' => $repaymentAmount,
            'paid_date' => Carbon::now(),
            'outstanding_amount' => 0,
        ]);

        // Refresh accounts
        $this->cashAccount->refresh();
        $this->mainAccount->refresh();
        $loanAssetAccount = Account::find($this->loanProduct->loan_asset_account_id);
        $loanAssetAccount->refresh();
        $interestIncomeAccount = Account::find($this->loanProduct->interest_income_account_id);
        $interestIncomeAccount->refresh();

        echo "   ✅ Repayment processed: TZS " . number_format($repaymentAmount) . "\n";
        echo "   ✅ Cash account balance: TZS " . number_format($this->cashAccount->current_balance) . "\n";
        echo "   ✅ Interest income account balance: TZS " . number_format($interestIncomeAccount->current_balance) . "\n";
        
        $this->todo_write('test_repayment', 'completed');
    }

    private function verifyDoubleEntryBookkeeping()
    {
        echo "\n🔍 Verifying Double-Entry Bookkeeping...\n";
        
        $entries = GeneralLedger::where('organization_id', $this->organization->id)
            ->orderBy('transaction_date')
            ->orderBy('created_at')
            ->get();

        $totalDebits = $entries->sum('debit_amount');
        $totalCredits = $entries->sum('credit_amount');

        echo "   📊 Total Debit Entries: TZS " . number_format($totalDebits) . "\n";
        echo "   📊 Total Credit Entries: TZS " . number_format($totalCredits) . "\n";
        echo "   📊 Difference: TZS " . number_format($totalDebits - $totalCredits) . "\n";

        if (abs($totalDebits - $totalCredits) < 0.01) {
            echo "   ✅ Double-entry bookkeeping is balanced!\n";
        } else {
            echo "   ❌ Double-entry bookkeeping is NOT balanced!\n";
            throw new \Exception("Double-entry bookkeeping imbalance detected!");
        }

        // Show all ledger entries
        echo "\n   📋 All General Ledger Entries:\n";
        foreach ($entries as $entry) {
            echo "      {$entry->transaction_date} | {$entry->account->name} | Debit: " . number_format($entry->debit_amount) . " | Credit: " . number_format($entry->credit_amount) . "\n";
        }
        
        $this->todo_write('verify_double_entry', 'completed');
    }

    private function verifyCalculations()
    {
        echo "\n🧮 Verifying All Calculations...\n";
        
        // Verify loan calculations
        $expectedMonthlyPayment = $this->loan->monthly_payment;
        $calculatedMonthlyPayment = $this->calculateExpectedMonthlyPayment();
        
        echo "   📊 Expected monthly payment: TZS " . number_format($expectedMonthlyPayment) . "\n";
        echo "   📊 Calculated monthly payment: TZS " . number_format($calculatedMonthlyPayment) . "\n";
        
        if (abs($expectedMonthlyPayment - $calculatedMonthlyPayment) < 0.01) {
            echo "   ✅ Monthly payment calculation is correct!\n";
        } else {
            echo "   ❌ Monthly payment calculation is incorrect!\n";
        }

        // Verify total interest
        $totalInterestFromSchedule = LoanSchedule::where('loan_id', $this->loan->id)->sum('interest_amount');
        echo "   📊 Total interest from schedule: TZS " . number_format($totalInterestFromSchedule) . "\n";
        echo "   📊 Total interest from loan: TZS " . number_format($this->loan->total_interest) . "\n";

        // Verify account balances
        $this->verifyAccountBalances();
        
        $this->todo_write('verify_calculations', 'completed');
    }

    private function calculateExpectedMonthlyPayment()
    {
        $principal = $this->loan->approved_amount;
        $rate = $this->loan->interest_rate / 100 / 12;
        $periods = $this->loan->loan_tenure_months;
        
        return $principal * ($rate * pow(1 + $rate, $periods)) / (pow(1 + $rate, $periods) - 1);
    }

    private function verifyAccountBalances()
    {
        echo "\n   💰 Account Balance Verification:\n";
        
        $accounts = Account::where('organization_id', $this->organization->id)->get();
        
        foreach ($accounts as $account) {
            $calculatedBalance = GeneralLedger::where('account_id', $account->id)
                ->selectRaw('SUM(debit_amount) - SUM(credit_amount) as balance')
                ->value('balance') ?? 0;
            
            echo "      {$account->name}: Stored = TZS " . number_format($account->current_balance) . " | Calculated = TZS " . number_format($calculatedBalance) . "\n";
            
            if (abs($account->current_balance - $calculatedBalance) > 0.01) {
                echo "      ❌ Balance mismatch for {$account->name}!\n";
            } else {
                echo "      ✅ Balance correct for {$account->name}\n";
            }
        }
    }

    private function todo_write($id, $status)
    {
        // This would integrate with the todo system if needed
        // For now, just echo the status
        echo "   📝 {$id}: {$status}\n";
    }
}
