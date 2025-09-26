<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;
use App\Models\GeneralLedger;
use Illuminate\Support\Facades\DB;

class FixAccountingIssues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounting:fix {--organization-id= : Organization ID to fix} {--dry-run : Show what would be fixed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix common accounting issues in the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $organizationId = $this->option('organization-id') ?? \App\Models\Organization::first()?->id;
        $dryRun = $this->option('dry-run');

        if (!$organizationId) {
            $this->error('No organization found. Please provide --organization-id or ensure at least one organization exists.');
            return 1;
        }

        if ($dryRun) {
            $this->info("DRY RUN: Would fix accounting issues for Organization ID: {$organizationId}");
        } else {
            $this->info("Fixing accounting issues for Organization ID: {$organizationId}");
        }
        
        $this->newLine();

        $fixed = 0;

        // 1. Fix account balances
        $fixed += $this->fixAccountBalances($organizationId, $dryRun);
        
        // 2. Fix orphaned transactions
        $fixed += $this->fixOrphanedTransactions($organizationId, $dryRun);
        
        // 3. Fix duplicate transaction IDs
        $fixed += $this->fixDuplicateTransactionIds($organizationId, $dryRun);
        
        // 4. Fix negative amounts
        $fixed += $this->fixNegativeAmounts($organizationId, $dryRun);

        $this->newLine();
        
        if ($dryRun) {
            $this->info("DRY RUN: Would fix {$fixed} issues.");
        } else {
            $this->info("Fixed {$fixed} accounting issues.");
        }
        
        return 0;
    }

    private function fixAccountBalances($organizationId, $dryRun)
    {
        $this->info('1. Fixing Account Balances...');
        
        $accounts = Account::where('organization_id', $organizationId)->get();
        $fixed = 0;
        
        foreach ($accounts as $account) {
            // Recalculate balance from general ledger
            $lastTransaction = GeneralLedger::where('account_id', $account->id)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            
            $correctBalance = $lastTransaction ? $lastTransaction->balance_after : $account->opening_balance;
            $currentBalance = $account->balance;
            
            if (abs($correctBalance - $currentBalance) > 0.01) {
                if ($dryRun) {
                    $this->line("   Would fix account '{$account->name}': {$currentBalance} → {$correctBalance}");
                } else {
                    $account->update(['balance' => $correctBalance]);
                    $this->line("   Fixed account '{$account->name}': {$currentBalance} → {$correctBalance}");
                }
                $fixed++;
            }
        }
        
        if ($fixed === 0) {
            $this->info('   ✓ All account balances are correct');
        } else {
            if (!$dryRun) {
                $this->info("   ✓ Fixed {$fixed} account balances");
            }
        }
        
        $this->newLine();
        return $fixed;
    }

    private function fixOrphanedTransactions($organizationId, $dryRun)
    {
        $this->info('2. Fixing Orphaned Transactions...');
        
        $fixed = 0;
        
        // Fix loan transactions without loans
        $orphanedLoans = GeneralLedger::where('organization_id', $organizationId)
            ->where('reference_type', 'App\\Models\\Loan')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('loans')
                    ->whereColumn('loans.id', 'general_ledger.reference_id');
            })
            ->get();
        
        foreach ($orphanedLoans as $transaction) {
            if ($dryRun) {
                $this->line("   Would remove orphaned loan transaction: {$transaction->transaction_id}");
            } else {
                $transaction->delete();
                $this->line("   Removed orphaned loan transaction: {$transaction->transaction_id}");
            }
            $fixed++;
        }
        
        // Fix fund transfer transactions without fund transfers
        $orphanedTransfers = GeneralLedger::where('organization_id', $organizationId)
            ->where('reference_type', 'FundTransfer')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('fund_transfers')
                    ->whereColumn('fund_transfers.id', 'general_ledger.reference_id');
            })
            ->get();
        
        foreach ($orphanedTransfers as $transaction) {
            if ($dryRun) {
                $this->line("   Would remove orphaned fund transfer transaction: {$transaction->transaction_id}");
            } else {
                $transaction->delete();
                $this->line("   Removed orphaned fund transfer transaction: {$transaction->transaction_id}");
            }
            $fixed++;
        }
        
        if ($fixed === 0) {
            $this->info('   ✓ No orphaned transactions found');
        } else {
            if (!$dryRun) {
                $this->info("   ✓ Removed {$fixed} orphaned transactions");
            }
        }
        
        $this->newLine();
        return $fixed;
    }

    private function fixDuplicateTransactionIds($organizationId, $dryRun)
    {
        $this->info('3. Fixing Duplicate Transaction IDs...');
        
        $duplicates = GeneralLedger::where('organization_id', $organizationId)
            ->select('transaction_id')
            ->groupBy('transaction_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        
        $fixed = 0;
        
        foreach ($duplicates as $duplicate) {
            $transactions = GeneralLedger::where('organization_id', $organizationId)
                ->where('transaction_id', $duplicate->transaction_id)
                ->orderBy('id')
                ->get();
            
            // Keep the first one, update the rest
            for ($i = 1; $i < $transactions->count(); $i++) {
                $transaction = $transactions[$i];
                $newTransactionId = $transaction->transaction_id . '-' . $i;
                
                if ($dryRun) {
                    $this->line("   Would rename duplicate transaction: {$transaction->transaction_id} → {$newTransactionId}");
                } else {
                    $transaction->update(['transaction_id' => $newTransactionId]);
                    $this->line("   Renamed duplicate transaction: {$transaction->transaction_id} → {$newTransactionId}");
                }
                $fixed++;
            }
        }
        
        if ($fixed === 0) {
            $this->info('   ✓ No duplicate transaction IDs found');
        } else {
            if (!$dryRun) {
                $this->info("   ✓ Fixed {$fixed} duplicate transaction IDs");
            }
        }
        
        $this->newLine();
        return $fixed;
    }

    private function fixNegativeAmounts($organizationId, $dryRun)
    {
        $this->info('4. Fixing Negative Amounts...');
        
        $negativeAmounts = GeneralLedger::where('organization_id', $organizationId)
            ->where('amount', '<', 0)
            ->get();
        
        $fixed = 0;
        
        foreach ($negativeAmounts as $transaction) {
            $newAmount = abs($transaction->amount);
            
            if ($dryRun) {
                $this->line("   Would fix negative amount: {$transaction->transaction_id} ({$transaction->amount} → {$newAmount})");
            } else {
                $transaction->update(['amount' => $newAmount]);
                $this->line("   Fixed negative amount: {$transaction->transaction_id} ({$transaction->amount} → {$newAmount})");
            }
            $fixed++;
        }
        
        if ($fixed === 0) {
            $this->info('   ✓ No negative amounts found');
        } else {
            if (!$dryRun) {
                $this->info("   ✓ Fixed {$fixed} negative amounts");
            }
        }
        
        $this->newLine();
        return $fixed;
    }
}
