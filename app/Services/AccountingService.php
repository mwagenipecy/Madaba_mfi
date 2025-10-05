<?php

namespace App\Services;

use App\Models\Account;
use App\Models\GeneralLedger;
use App\Models\Loan;
use App\Models\LoanTransaction;
use App\Models\ExpenseRequest;
use App\Models\FundTransfer;
use App\Models\AccountRecharge;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Record loan disbursement accounting entries
     */
    public function recordLoanDisbursement(Loan $loan, Account $disbursementAccount, float $amount): bool
    {
        try {
            DB::beginTransaction();

            $transactionId = 'DISB-' . $loan->loan_number . '-' . date('YmdHis');
            
            // Get loan portfolio account (Asset)
            $loanPortfolioAccount = Account::where('organization_id', $loan->organization_id)
                ->where('name', 'like', '%Loan Portfolio%')
                ->whereHas('accountType', function($query) {
                    $query->where('category', 'asset');
                })
                ->first();

            if (!$loanPortfolioAccount) {
                throw new \Exception('Loan Portfolio account not found');
            }

            // Debit: Loan Portfolio (Asset increases)
            GeneralLedger::createTransaction(
                $transactionId . '-LOAN-ASSET',
                $loanPortfolioAccount,
                'debit',
                $amount,
                "Loan disbursement for {$loan->loan_number}",
                'App\\Models\\Loan',
                $loan->id,
                auth()->id()
            );

            // Credit: Disbursement Account (Liability decreases - money going out)
            GeneralLedger::createTransaction(
                $transactionId . '-DISB-LIABILITY',
                $disbursementAccount,
                'credit',
                $amount,
                "Loan disbursement for {$loan->loan_number}",
                'App\\Models\\Loan',
                $loan->id,
                auth()->id()
            );

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Record loan repayment accounting entries
     */
    public function recordLoanRepayment(Loan $loan, Account $collectionAccount, float $totalAmount, float $principalAmount, float $interestAmount): bool
    {
        try {
            DB::beginTransaction();

            $transactionId = 'REP-' . $loan->loan_number . '-' . date('YmdHis');
            
            // Get loan product accounts
            $loanProduct = $loan->loanProduct;
            $principalAccount = $loanProduct->principalAccount;
            $interestRevenueAccount = $loanProduct->interestRevenueAccount;
            
            if (!$principalAccount || !$interestRevenueAccount) {
                throw new \Exception('Loan product accounts not configured properly');
            }

            // Debit: Collection Account (Cash received - Asset increases)
            GeneralLedger::createTransaction(
                $transactionId . '-COLLECTION',
                $collectionAccount,
                'debit',
                $totalAmount,
                "Loan repayment received - {$loan->loan_number}",
                'LoanTransaction',
                $loan->id,
                auth()->id()
            );

            // Credit: Principal Account (Principal portion - Asset decreases)
            if ($principalAmount > 0) {
                GeneralLedger::createTransaction(
                    $transactionId . '-PRINCIPAL',
                    $principalAccount,
                    'credit',
                    $principalAmount,
                    "Principal repayment - {$loan->loan_number}",
                    'LoanTransaction',
                    $loan->id,
                    auth()->id()
                );
            }

            // Credit: Interest Revenue Account (Interest portion - Income increases)
            if ($interestAmount > 0) {
                GeneralLedger::createTransaction(
                    $transactionId . '-INTEREST',
                    $interestRevenueAccount,
                    'credit',
                    $interestAmount,
                    "Interest income - {$loan->loan_number}",
                    'LoanTransaction',
                    $loan->id,
                    auth()->id()
                );
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Record expense request accounting entries
     */
    public function recordExpensePayment(ExpenseRequest $expenseRequest): bool
    {
        try {
            DB::beginTransaction();

            $transactionId = 'EXP-' . $expenseRequest->request_number . '-' . date('YmdHis');
            $amount = $expenseRequest->amount;

            // Debit: Expense Account (Expense increases)
            GeneralLedger::createTransaction(
                $transactionId . '-EXPENSE',
                $expenseRequest->expenseAccount,
                'debit',
                $amount,
                "Expense payment - {$expenseRequest->request_number}",
                'ExpenseRequest',
                $expenseRequest->id,
                auth()->id()
            );

            // Credit: Payment Account (Asset decreases - money going out)
            GeneralLedger::createTransaction(
                $transactionId . '-PAYMENT',
                $expenseRequest->paymentAccount,
                'credit',
                $amount,
                "Expense payment - {$expenseRequest->request_number}",
                'ExpenseRequest',
                $expenseRequest->id,
                auth()->id()
            );

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Record fund transfer accounting entries
     */
    public function recordFundTransfer(FundTransfer $fundTransfer): bool
    {
        try {
            DB::beginTransaction();

            $transactionId = 'FT-' . $fundTransfer->transfer_number . '-' . date('YmdHis');
            $amount = $fundTransfer->amount;

            // For fund transfers between asset accounts (e.g., bank to cash, bank to bank):
            // Debit: Destination Account (Asset increases - money coming in)
            GeneralLedger::createTransaction(
                $transactionId . '-DESTINATION',
                $fundTransfer->toAccount,
                'debit',
                $amount,
                "Fund transfer from {$fundTransfer->fromAccount->name}",
                'FundTransfer',
                $fundTransfer->id,
                $fundTransfer->approved_by
            );

            // Credit: Source Account (Asset decreases - money going out)
            GeneralLedger::createTransaction(
                $transactionId . '-SOURCE',
                $fundTransfer->fromAccount,
                'credit',
                $amount,
                "Fund transfer to {$fundTransfer->toAccount->name}",
                'FundTransfer',
                $fundTransfer->id,
                $fundTransfer->approved_by
            );

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Record account recharge accounting entries (Capital Introduction)
     */
    public function recordAccountRecharge(AccountRecharge $accountRecharge): bool
    {
        try {
            DB::beginTransaction();

            $transactionId = 'RC-' . $accountRecharge->recharge_number . '-' . date('YmdHis');
            $amount = $accountRecharge->recharge_amount;

            // Get giver account from metadata
            $giverAccountId = $accountRecharge->metadata['giver_account_id'] ?? null;
            $giverAccount = null;
            
            if ($giverAccountId) {
                $giverAccount = Account::find($giverAccountId);
                if (!$giverAccount) {
                    throw new \Exception('Giver account not found');
                }
            }

            // For capital injection from giver account to capital account:
            // Debit: Capital Account (Asset increases - money coming into the system)
            GeneralLedger::createTransaction(
                $transactionId . '-CAPITAL',
                $accountRecharge->mainAccount,
                'debit',
                $amount,
                "Capital injection: {$accountRecharge->description}",
                'AccountRecharge',
                $accountRecharge->id,
                $accountRecharge->approved_by
            );

            if ($giverAccount) {
                // Credit: Giver Account (Liability decreases - money coming from external source)
                // Giver accounts have negative/credit balance, so crediting reduces the liability
                GeneralLedger::createTransaction(
                    $transactionId . '-GIVER',
                    $giverAccount,
                    'credit',
                    $amount,
                    "Capital injection from external source: {$accountRecharge->description}",
                    'AccountRecharge',
                    $accountRecharge->id,
                    $accountRecharge->approved_by
                );
            } else {
                // Fallback: Credit Equity Account if no giver account specified
                $equityAccount = Account::where('organization_id', $accountRecharge->mainAccount->organization_id)
                    ->whereHas('accountType', function($query) {
                        $query->where('category', 'equity');
                    })
                    ->where('name', 'like', '%Capital%')
                    ->first();

                if (!$equityAccount) {
                    throw new \Exception('Equity account not found for capital introduction');
                }

                GeneralLedger::createTransaction(
                    $transactionId . '-EQUITY',
                    $equityAccount,
                    'credit',
                    $amount,
                    "Capital introduction: {$accountRecharge->description}",
                    'AccountRecharge',
                    $accountRecharge->id,
                    $accountRecharge->approved_by
                );
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Record year-end closing entries
     */
    public function recordYearEndClosing($organizationId, $fiscalYear): bool
    {
        try {
            DB::beginTransaction();

            $transactionId = 'YEC-' . $fiscalYear . '-' . date('YmdHis');

            // Get all revenue and expense accounts
            $revenueAccounts = Account::where('organization_id', $organizationId)
                ->whereHas('accountType', function($query) {
                    $query->where('category', 'revenue');
                })
                ->get();

            $expenseAccounts = Account::where('organization_id', $organizationId)
                ->whereHas('accountType', function($query) {
                    $query->where('category', 'expense');
                })
                ->get();

            // Get income summary account
            $incomeSummaryAccount = Account::where('organization_id', $organizationId)
                ->where('name', 'like', '%Income Summary%')
                ->first();

            if (!$incomeSummaryAccount) {
                throw new \Exception('Income Summary account not found');
            }

            $totalRevenue = 0;
            $totalExpenses = 0;

            // Close revenue accounts to income summary
            foreach ($revenueAccounts as $account) {
                $balance = $this->calculateAccountBalance($account);
                if ($balance > 0) {
                    // Debit: Revenue Account (close to zero)
                    GeneralLedger::createTransaction(
                        $transactionId . '-REV-' . $account->id,
                        $account,
                        'debit',
                        $balance,
                        "Year-end closing: Revenue account closed",
                        'YearEndClosing',
                        $fiscalYear,
                        auth()->id()
                    );

                    // Credit: Income Summary Account
                    GeneralLedger::createTransaction(
                        $transactionId . '-REV-SUMMARY-' . $account->id,
                        $incomeSummaryAccount,
                        'credit',
                        $balance,
                        "Year-end closing: Revenue from {$account->name}",
                        'YearEndClosing',
                        $fiscalYear,
                        auth()->id()
                    );

                    $totalRevenue += $balance;
                }
            }

            // Close expense accounts to income summary
            foreach ($expenseAccounts as $account) {
                $balance = $this->calculateAccountBalance($account);
                if ($balance > 0) {
                    // Credit: Expense Account (close to zero)
                    GeneralLedger::createTransaction(
                        $transactionId . '-EXP-' . $account->id,
                        $account,
                        'credit',
                        $balance,
                        "Year-end closing: Expense account closed",
                        'YearEndClosing',
                        $fiscalYear,
                        auth()->id()
                    );

                    // Debit: Income Summary Account
                    GeneralLedger::createTransaction(
                        $transactionId . '-EXP-SUMMARY-' . $account->id,
                        $incomeSummaryAccount,
                        'debit',
                        $balance,
                        "Year-end closing: Expense from {$account->name}",
                        'YearEndClosing',
                        $fiscalYear,
                        auth()->id()
                    );

                    $totalExpenses += $balance;
                }
            }

            // Transfer net income/loss to retained earnings
            $netIncome = $totalRevenue - $totalExpenses;
            $retainedEarningsAccount = Account::where('organization_id', $organizationId)
                ->where('name', 'like', '%Retained Earnings%')
                ->first();

            if ($retainedEarningsAccount) {
                if ($netIncome > 0) {
                    // Net income: Debit Income Summary, Credit Retained Earnings
                    GeneralLedger::createTransaction(
                        $transactionId . '-NET-INCOME',
                        $incomeSummaryAccount,
                        'debit',
                        $netIncome,
                        "Year-end closing: Net income transferred to retained earnings",
                        'YearEndClosing',
                        $fiscalYear,
                        auth()->id()
                    );

                    GeneralLedger::createTransaction(
                        $transactionId . '-RETAINED-EARNINGS',
                        $retainedEarningsAccount,
                        'credit',
                        $netIncome,
                        "Year-end closing: Net income for fiscal year {$fiscalYear}",
                        'YearEndClosing',
                        $fiscalYear,
                        auth()->id()
                    );
                } else {
                    // Net loss: Credit Income Summary, Debit Retained Earnings
                    $netLoss = abs($netIncome);
                    GeneralLedger::createTransaction(
                        $transactionId . '-NET-LOSS',
                        $incomeSummaryAccount,
                        'credit',
                        $netLoss,
                        "Year-end closing: Net loss transferred to retained earnings",
                        'YearEndClosing',
                        $fiscalYear,
                        auth()->id()
                    );

                    GeneralLedger::createTransaction(
                        $transactionId . '-RETAINED-EARNINGS-LOSS',
                        $retainedEarningsAccount,
                        'debit',
                        $netLoss,
                        "Year-end closing: Net loss for fiscal year {$fiscalYear}",
                        'YearEndClosing',
                        $fiscalYear,
                        auth()->id()
                    );
                }
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Record loan write-off accounting entries
     */
    public function recordLoanWriteOff(Loan $loan, float $amount): bool
    {
        try {
            DB::beginTransaction();

            $transactionId = 'WO-' . $loan->loan_number . '-' . date('YmdHis');

            // Get loan portfolio account (Asset)
            $loanPortfolioAccount = Account::where('organization_id', $loan->organization_id)
                ->where('name', 'like', '%Loan Portfolio%')
                ->whereHas('accountType', function($query) {
                    $query->where('category', 'asset');
                })
                ->first();

            // Get expense account for write-offs
            $expenseAccount = Account::where('organization_id', $loan->organization_id)
                ->whereHas('accountType', function($query) {
                    $query->where('category', 'expense');
                })
                ->where('name', 'like', '%Bad Debt%')
                ->first();

            if (!$loanPortfolioAccount || !$expenseAccount) {
                throw new \Exception('Required accounts for write-off not found');
            }

            // Credit: Loan Portfolio (Asset decreases)
            GeneralLedger::createTransaction(
                $transactionId . '-LOAN',
                $loanPortfolioAccount,
                'credit',
                $amount,
                "Loan write-off for {$loan->loan_number}",
                'App\\Models\\Loan',
                $loan->id,
                auth()->id()
            );

            // Debit: Bad Debt Expense (Expense increases)
            GeneralLedger::createTransaction(
                $transactionId . '-EXPENSE',
                $expenseAccount,
                'debit',
                $amount,
                "Bad debt expense for {$loan->loan_number}",
                'App\\Models\\Loan',
                $loan->id,
                auth()->id()
            );

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate account balance as of specific date
     */
    public function calculateAccountBalance(Account $account, $asOfDate = null): float
    {
        $asOfDate = $asOfDate ?: now();

        // Use the balance_after from the last transaction entry for accurate balance
        $lastTransaction = GeneralLedger::where('account_id', $account->id)
            ->where('transaction_date', '<=', $asOfDate)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransaction) {
            return $lastTransaction->balance_after;
        }

        // If no transactions, return the account's current balance
        return $account->balance;
    }

    /**
     * Get financial position summary
     */
    public function getFinancialPosition($organizationId, $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?: now();

        $accounts = Account::where('organization_id', $organizationId)
            ->with(['accountType'])
            ->get();

        $totals = [
            'assets' => 0,
            'liabilities' => 0,
            'equity' => 0,
            'income' => 0,
            'expenses' => 0,
        ];

        foreach ($accounts as $account) {
            $balance = $this->calculateAccountBalance($account, $asOfDate);
            $category = $account->accountType->category;

            if (isset($totals[$category])) {
                $totals[$category] += $balance;
            }
        }

        // Calculate net worth
        $netWorth = $totals['assets'] - $totals['liabilities'];
        $totals['net_worth'] = $netWorth;

        return $totals;
    }

    /**
     * Validate accounting equation: Assets = Liabilities + Equity
     */
    public function validateAccountingEquation($organizationId, $asOfDate = null): array
    {
        $position = $this->getFinancialPosition($organizationId, $asOfDate);
        
        $assets = $position['assets'];
        $liabilities = $position['liabilities'];
        $equity = $position['equity'];
        
        $equationBalance = $liabilities + $equity;
        $difference = abs($assets - $equationBalance);
        
        return [
            'is_balanced' => $difference < 0.01,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'equation_balance' => $equationBalance,
            'difference' => $difference,
            'status' => $difference < 0.01 ? 'BALANCED' : 'OUT_OF_BALANCE'
        ];
    }
}
