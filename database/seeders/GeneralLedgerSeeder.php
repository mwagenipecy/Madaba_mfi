<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Branch;
use App\Models\GeneralLedger;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;

class GeneralLedgerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organization = Organization::first();
        $branches = Branch::all();
        $accountTypes = AccountType::all();
        
        if (!$organization || $branches->isEmpty() || $accountTypes->isEmpty()) {
            $this->command->error('Required data not found. Please run other seeders first.');
            return;
        }

        // Get or create a user for transactions
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'System Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'organization_id' => $organization->id,
            ]);
        }

        // Create branch-specific accounts
        $this->createBranchAccounts($organization, $branches, $accountTypes);

        // Get all accounts
        $allAccounts = Account::with('accountType', 'branch')->get();
        
        $this->command->info('Creating general ledger transactions...');

        // Generate transactions for the last 30 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        // Create various types of transactions
        $this->createLoanDisbursements($allAccounts, $organization, $user, $startDate, $endDate);
        $this->createLoanRepayments($allAccounts, $organization, $user, $startDate, $endDate);
        $this->createAccountRecharges($allAccounts, $organization, $user, $startDate, $endDate);

        $this->command->info('General ledger data created successfully!');
        $this->command->info('Total transactions: ' . GeneralLedger::count());
    }

    private function createBranchAccounts($organization, $branches, $accountTypes)
    {
        $this->command->info('Creating branch-specific accounts...');

        foreach ($branches as $branch) {
            // Create branch liability account (for deposits)
            $liabilityType = $accountTypes->where('name', 'Liability')->first();
            if ($liabilityType) {
                Account::firstOrCreate([
                    'name' => $branch->name . ' Deposits',
                    'account_type_id' => $liabilityType->id,
                    'organization_id' => $organization->id,
                    'branch_id' => $branch->id,
                ], [
                    'account_number' => 'DEP-' . strtoupper(substr($branch->name, 0, 3)),
                    'description' => 'Customer deposits for ' . $branch->name,
                    'balance' => 0,
                    'currency' => 'TZS',
                ]);
            }

            // Create branch asset account (for cash)
            $assetType = $accountTypes->where('name', 'Assets')->first();
            if ($assetType) {
                Account::firstOrCreate([
                    'name' => $branch->name . ' Cash',
                    'account_type_id' => $assetType->id,
                    'organization_id' => $organization->id,
                    'branch_id' => $branch->id,
                ], [
                    'account_number' => 'CASH-' . strtoupper(substr($branch->name, 0, 3)),
                    'description' => 'Cash on hand for ' . $branch->name,
                    'balance' => 0,
                    'currency' => 'TZS',
                ]);
            }

            // Create branch loan portfolio account
            if ($assetType) {
                Account::firstOrCreate([
                    'name' => $branch->name . ' Loan Portfolio',
                    'account_type_id' => $assetType->id,
                    'organization_id' => $organization->id,
                    'branch_id' => $branch->id,
                ], [
                    'account_number' => 'LOAN-' . strtoupper(substr($branch->name, 0, 3)),
                    'description' => 'Outstanding loans for ' . $branch->name,
                    'balance' => 0,
                    'currency' => 'TZS',
                ]);
            }
        }
    }

    private function createLoanDisbursements($accounts, $organization, $user, $startDate, $endDate)
    {
        $this->command->info('Creating loan disbursement transactions...');

        $loanPortfolioAccounts = $accounts->filter(function($account) {
            return str_contains($account->name, 'Loan Portfolio');
        });
        $liabilityAccounts = $accounts->filter(function($account) {
            return $account->accountType->name === 'Liability';
        });

        for ($i = 0; $i < 15; $i++) {
            $date = $this->randomDate($startDate, $endDate);
            $amount = rand(50000, 500000); // 50,000 to 500,000 TZS
            $loanAccount = $loanPortfolioAccounts->random();
            $liabilityAccount = $liabilityAccounts->random();

            // Debit: Loan Portfolio (Asset)
            $this->createTransaction(
                'LOAN-DISB-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT) . '-D',
                $loanAccount,
                'debit',
                $amount,
                'Loan disbursement #' . ($i + 1),
                $organization,
                $user,
                $date
            );

            // Credit: Liability Account
            $this->createTransaction(
                'LOAN-DISB-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT) . '-C',
                $liabilityAccount,
                'credit',
                $amount,
                'Loan disbursement #' . ($i + 1),
                $organization,
                $user,
                $date
            );
        }
    }

    private function createLoanRepayments($accounts, $organization, $user, $startDate, $endDate)
    {
        $this->command->info('Creating loan repayment transactions...');

        $loanPortfolioAccounts = $accounts->filter(function($account) {
            return str_contains($account->name, 'Loan Portfolio');
        });
        $cashAccounts = $accounts->filter(function($account) {
            return str_contains($account->name, 'Cash');
        });

        for ($i = 0; $i < 20; $i++) {
            $date = $this->randomDate($startDate, $endDate);
            $amount = rand(10000, 100000); // 10,000 to 100,000 TZS
            $loanAccount = $loanPortfolioAccounts->random();
            $cashAccount = $cashAccounts->random();

            // Debit: Cash Account (Asset)
            $this->createTransaction(
                'LOAN-REPAY-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT) . '-D',
                $cashAccount,
                'debit',
                $amount,
                'Loan repayment #' . ($i + 1),
                $organization,
                $user,
                $date
            );

            // Credit: Loan Portfolio (Asset)
            $this->createTransaction(
                'LOAN-REPAY-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT) . '-C',
                $loanAccount,
                'credit',
                $amount,
                'Loan repayment #' . ($i + 1),
                $organization,
                $user,
                $date
            );
        }
    }

    private function createAccountRecharges($accounts, $organization, $user, $startDate, $endDate)
    {
        $this->command->info('Creating account recharge transactions...');

        $depositAccounts = $accounts->filter(function($account) {
            return str_contains($account->name, 'Deposits');
        });
        $cashAccounts = $accounts->filter(function($account) {
            return str_contains($account->name, 'Cash');
        });

        for ($i = 0; $i < 25; $i++) {
            $date = $this->randomDate($startDate, $endDate);
            $amount = rand(5000, 50000); // 5,000 to 50,000 TZS
            $depositAccount = $depositAccounts->random();
            $cashAccount = $cashAccounts->random();

            // Debit: Cash Account (Asset)
            $this->createTransaction(
                'RECHARGE-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT) . '-D',
                $cashAccount,
                'debit',
                $amount,
                'Account recharge #' . ($i + 1),
                $organization,
                $user,
                $date
            );

            // Credit: Deposits Account (Liability)
            $this->createTransaction(
                'RECHARGE-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT) . '-C',
                $depositAccount,
                'credit',
                $amount,
                'Account recharge #' . ($i + 1),
                $organization,
                $user,
                $date
            );
        }
    }

    private function createTransaction($transactionId, $account, $type, $amount, $description, $organization, $user, $date)
    {
        $newBalance = $type === 'debit' 
            ? $account->balance + $amount 
            : $account->balance - $amount;

        GeneralLedger::create([
            'organization_id' => $organization->id,
            'branch_id' => $account->branch_id,
            'transaction_id' => $transactionId,
            'transaction_date' => $date,
            'account_id' => $account->id,
            'transaction_type' => $type,
            'amount' => $amount,
            'currency' => 'TZS',
            'description' => $description,
            'created_by' => $user->id,
            'balance_after' => $newBalance,
        ]);

        // Update account balance
        $account->update(['balance' => $newBalance]);
    }

    private function randomDate($startDate, $endDate)
    {
        $start = $startDate->timestamp;
        $end = $endDate->timestamp;
        $random = rand($start, $end);
        return Carbon::createFromTimestamp($random);
    }
}
