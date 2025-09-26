<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;
use App\Models\GeneralLedger;
use App\Services\AccountingService;
use Illuminate\Support\Facades\DB;

class ValidateAccountingSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounting:validate {--organization-id= : Organization ID to validate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate the accounting system for balance and consistency';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $organizationId = $this->option('organization-id') ?? \App\Models\Organization::first()?->id;

        if (!$organizationId) {
            $this->error('No organization found. Please provide --organization-id or ensure at least one organization exists.');
            return 1;
        }

        $this->info("Validating accounting system for Organization ID: {$organizationId}");
        $this->newLine();

        $accountingService = new AccountingService();
        
        // 1. Validate accounting equation
        $this->validateAccountingEquation($accountingService, $organizationId);
        
        // 2. Validate account balances
        $this->validateAccountBalances($organizationId);
        
        // 3. Validate general ledger consistency
        $this->validateGeneralLedgerConsistency($organizationId);
        
        // 4. Check for orphaned transactions
        $this->checkOrphanedTransactions($organizationId);
        
        // 5. Validate loan accounting
        $this->validateLoanAccounting($organizationId);

        $this->newLine();
        $this->info('Accounting system validation completed.');
        
        return 0;
    }

    private function validateAccountingEquation(AccountingService $service, $organizationId)
    {
        $this->info('1. Validating Accounting Equation (Assets = Liabilities + Equity)...');
        
        $equation = $service->validateAccountingEquation($organizationId);
        
        if ($equation['is_balanced']) {
            $this->info('   ✓ Accounting equation is balanced');
        } else {
            $this->error('   ✗ Accounting equation is NOT balanced');
            $this->error("     Assets: " . number_format($equation['assets'], 2));
            $this->error("     Liabilities: " . number_format($equation['liabilities'], 2));
            $this->error("     Equity: " . number_format($equation['equity'], 2));
            $this->error("     Difference: " . number_format($equation['difference'], 2));
        }
        
        $this->newLine();
    }

    private function validateAccountBalances($organizationId)
    {
        $this->info('2. Validating Account Balances...');
        
        $accounts = Account::where('organization_id', $organizationId)
            ->with(['accountType'])
            ->get();

        $inconsistencies = 0;
        
        foreach ($accounts as $account) {
            // Get last transaction balance
            $lastTransaction = GeneralLedger::where('account_id', $account->id)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            
            $expectedBalance = $lastTransaction ? $lastTransaction->balance_after : $account->opening_balance;
            $actualBalance = $account->balance;
            
            if (abs($expectedBalance - $actualBalance) > 0.01) {
                $inconsistencies++;
                $this->error("   ✗ Account '{$account->name}' balance mismatch:");
                $this->error("     Expected: " . number_format($expectedBalance, 2));
                $this->error("     Actual: " . number_format($actualBalance, 2));
                $this->error("     Difference: " . number_format($expectedBalance - $actualBalance, 2));
            }
        }
        
        if ($inconsistencies === 0) {
            $this->info('   ✓ All account balances are consistent');
        } else {
            $this->error("   ✗ Found {$inconsistencies} account balance inconsistencies");
        }
        
        $this->newLine();
    }

    private function validateGeneralLedgerConsistency($organizationId)
    {
        $this->info('3. Validating General Ledger Consistency...');
        
        $issues = 0;
        
        // Check for duplicate transaction IDs
        $duplicates = GeneralLedger::where('organization_id', $organizationId)
            ->select('transaction_id')
            ->groupBy('transaction_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        
        if ($duplicates->count() > 0) {
            $issues += $duplicates->count();
            $this->error("   ✗ Found {$duplicates->count()} duplicate transaction IDs");
        }
        
        // Check for missing account references
        $missingAccounts = GeneralLedger::where('organization_id', $organizationId)
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('accounts')
                    ->whereColumn('accounts.id', 'general_ledger.account_id');
            })
            ->count();
        
        if ($missingAccounts > 0) {
            $issues += $missingAccounts;
            $this->error("   ✗ Found {$missingAccounts} ledger entries with missing account references");
        }
        
        // Check for negative amounts
        $negativeAmounts = GeneralLedger::where('organization_id', $organizationId)
            ->where('amount', '<', 0)
            ->count();
        
        if ($negativeAmounts > 0) {
            $issues += $negativeAmounts;
            $this->error("   ✗ Found {$negativeAmounts} ledger entries with negative amounts");
        }
        
        if ($issues === 0) {
            $this->info('   ✓ General ledger is consistent');
        } else {
            $this->error("   ✗ Found {$issues} general ledger issues");
        }
        
        $this->newLine();
    }

    private function checkOrphanedTransactions($organizationId)
    {
        $this->info('4. Checking for Orphaned Transactions...');
        
        $orphaned = 0;
        
        // Check loan transactions without loans
        $orphaned += GeneralLedger::where('organization_id', $organizationId)
            ->where('reference_type', 'App\\Models\\Loan')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('loans')
                    ->whereColumn('loans.id', 'general_ledger.reference_id');
            })
            ->count();
        
        // Check fund transfer transactions without fund transfers
        $orphaned += GeneralLedger::where('organization_id', $organizationId)
            ->where('reference_type', 'FundTransfer')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('fund_transfers')
                    ->whereColumn('fund_transfers.id', 'general_ledger.reference_id');
            })
            ->count();
        
        if ($orphaned > 0) {
            $this->error("   ✗ Found {$orphaned} orphaned transactions");
        } else {
            $this->info('   ✓ No orphaned transactions found');
        }
        
        $this->newLine();
    }

    private function validateLoanAccounting($organizationId)
    {
        $this->info('5. Validating Loan Accounting...');
        
        $issues = 0;
        
        // Check loans with missing product account configurations
        $loansWithoutAccounts = \App\Models\Loan::where('organization_id', $organizationId)
            ->whereHas('loanProduct', function($query) {
                $query->where(function($q) {
                    $q->whereNull('disbursement_account_id')
                      ->orWhereNull('collection_account_id')
                      ->orWhereNull('interest_revenue_account_id')
                      ->orWhereNull('principal_account_id');
                });
            })
            ->count();
        
        if ($loansWithoutAccounts > 0) {
            $issues += $loansWithoutAccounts;
            $this->error("   ✗ Found {$loansWithoutAccounts} loans with incomplete account configurations");
        }
        
        // Check for loans with negative outstanding balances
        $negativeBalances = \App\Models\Loan::where('organization_id', $organizationId)
            ->where('outstanding_balance', '<', 0)
            ->count();
        
        if ($negativeBalances > 0) {
            $issues += $negativeBalances;
            $this->error("   ✗ Found {$negativeBalances} loans with negative outstanding balances");
        }
        
        if ($issues === 0) {
            $this->info('   ✓ Loan accounting is valid');
        } else {
            $this->error("   ✗ Found {$issues} loan accounting issues");
        }
        
        $this->newLine();
    }
}
