<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\LoanTransaction;
use Illuminate\Support\Facades\DB;

class FixLoanSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:fix-schedules {--organization-id= : Organization ID to fix} {--dry-run : Show what would be fixed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix loan schedules based on actual repayment transactions';

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
            $this->info("DRY RUN: Would fix loan schedules for Organization ID: {$organizationId}");
        } else {
            $this->info("Fixing loan schedules for Organization ID: {$organizationId}");
        }
        
        $this->newLine();

        $fixed = 0;

        // Get all loans with repayment transactions
        $loans = Loan::where('organization_id', $organizationId)
            ->whereHas('transactions', function($query) {
                $query->whereIn('transaction_type', ['principal_payment', 'interest_payment'])
                      ->where('status', 'completed');
            })
            ->with(['schedules', 'transactions' => function($query) {
                $query->whereIn('transaction_type', ['principal_payment', 'interest_payment'])
                      ->where('status', 'completed')
                      ->orderBy('transaction_date');
            }])
            ->get();

        foreach ($loans as $loan) {
            $this->line("Processing loan: {$loan->loan_number}");
            
            // Get all repayment transactions for this loan
            $repayments = $loan->transactions->whereIn('transaction_type', ['principal_payment', 'interest_payment']);
            
            // Reset all schedules to pending
            foreach ($loan->schedules as $schedule) {
                $originalStatus = $schedule->status;
                $originalPaidAmount = $schedule->paid_amount;
                $originalPaidDate = $schedule->paid_date;
                
                // Calculate what the schedule should be based on transactions
                $scheduleTransactions = $repayments->where('loan_schedule_id', $schedule->id);
                $totalPaid = $scheduleTransactions->sum('amount');
                
                if ($totalPaid > 0) {
                    $schedule->paid_amount = $totalPaid;
                    
                    if ($totalPaid >= $schedule->total_amount) {
                        $schedule->status = 'paid';
                        $schedule->paid_date = $scheduleTransactions->last()->transaction_date;
                    } else {
                        $schedule->status = 'partial';
                        $schedule->paid_date = null;
                    }
                    
                    $schedule->outstanding_amount = $schedule->total_amount - $schedule->paid_amount;
                    
                    if (!$dryRun) {
                        $schedule->save();
                    }
                    
                    if ($originalStatus !== $schedule->status || $originalPaidAmount != $schedule->paid_amount) {
                        $fixed++;
                        $this->line("  - Schedule {$schedule->installment_number}: {$originalStatus} → {$schedule->status}, Amount: {$originalPaidAmount} → {$schedule->paid_amount}");
                    }
                } else {
                    // No transactions for this schedule, reset to pending
                    if ($schedule->status !== 'pending') {
                        $schedule->status = 'pending';
                        $schedule->paid_amount = 0;
                        $schedule->paid_date = null;
                        $schedule->outstanding_amount = $schedule->total_amount;
                        
                        if (!$dryRun) {
                            $schedule->save();
                        }
                        
                        $fixed++;
                        $this->line("  - Schedule {$schedule->installment_number}: {$originalStatus} → pending, Amount: {$originalPaidAmount} → 0");
                    }
                }
            }
        }

        $this->newLine();
        
        if ($dryRun) {
            $this->info("DRY RUN: Would fix {$fixed} loan schedule issues.");
        } else {
            $this->info("Fixed {$fixed} loan schedule issues.");
        }
        
        return 0;
    }
}
