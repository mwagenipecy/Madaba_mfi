<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'loan_number',
        'client_id',
        'loan_product_id',
        'organization_id',
        'branch_id',
        'loan_officer_id',
        'loan_amount',
        'approved_amount',
        'interest_rate',
        'interest_calculation_method',
        'loan_tenure_months',
        'repayment_frequency',
        'application_date',
        'approval_date',
        'disbursement_date',
        'first_payment_date',
        'maturity_date',
        'total_interest',
        'total_amount',
        'monthly_payment',
        'processing_fee',
        'insurance_fee',
        'late_fee',
        'penalty_fee',
        'other_fees',
        'status',
        'approval_status',
        'approved_by',
        'rejected_by',
        'approval_notes',
        'rejection_reason',
        'paid_amount',
        'outstanding_balance',
        'overdue_amount',
        'overdue_days',
        'payments_made',
        'total_payments',
        'requires_collateral',
        'collateral_description',
        'collateral_value',
        'collateral_location',
        'loan_purpose',
        'guarantor_name',
        'guarantor_phone',
        'guarantor_address',
        'notes',
        'metadata',
        'disbursement_account_id',
        'disbursement_reference',
        'returned_at',
        'returned_by',
        'closure_date',
        'closure_reason',
        'closed_by',
        'is_restructured',
        'original_loan_id',
        'restructure_reason',
        'restructured_by',
        'restructure_date',
        'is_top_up',
        'original_loan_id_for_topup',
        'top_up_amount',
        'top_up_date',
        'top_up_processed_by',
        'write_off_amount',
        'write_off_date',
        'write_off_reason',
        'write_off_by',
        'documents',
        'comments',
    ];

    protected $casts = [
        'loan_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'total_interest' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'insurance_fee' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'penalty_fee' => 'decimal:2',
        'other_fees' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'overdue_amount' => 'decimal:2',
        'collateral_value' => 'decimal:2',
        'top_up_amount' => 'decimal:2',
        'write_off_amount' => 'decimal:2',
        'application_date' => 'date',
        'approval_date' => 'date',
        'disbursement_date' => 'date',
        'first_payment_date' => 'date',
        'maturity_date' => 'date',
        'closure_date' => 'date',
        'restructure_date' => 'date',
        'top_up_date' => 'date',
        'write_off_date' => 'date',
        'requires_collateral' => 'boolean',
        'is_restructured' => 'boolean',
        'is_top_up' => 'boolean',
        'metadata' => 'array',
        'documents' => 'array',
        'comments' => 'array',
        'payments_made' => 'integer',
        'total_payments' => 'integer',
        'overdue_days' => 'integer',
        'loan_tenure_months' => 'integer',
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function loanProduct(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function loanOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'loan_officer_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function disbursementAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'disbursement_account_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function originalLoan(): BelongsTo
    {
        return $this->belongsTo(Loan::class, 'original_loan_id');
    }

    public function restructuredLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'original_loan_id');
    }

    public function restructuredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restructured_by');
    }

    public function originalLoanForTopup(): BelongsTo
    {
        return $this->belongsTo(Loan::class, 'original_loan_id_for_topup');
    }

    public function topUpLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'original_loan_id_for_topup');
    }

    public function topUpProcessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'top_up_processed_by');
    }

    public function writeOffBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'write_off_by');
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(LoanSchedule::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LoanDocument::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoanTransaction::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByLoanOfficer($query, $officerId)
    {
        return $query->where('loan_officer_id', $officerId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('application_date', [$startDate, $endDate]);
    }

    public function scopeByYear($query, $year)
    {
        return $query->whereYear('application_date', $year);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeWrittenOff($query)
    {
        return $query->where('status', 'written_off');
    }

    public function scopeRestructured($query)
    {
        return $query->where('is_restructured', true);
    }

    public function scopeTopUp($query)
    {
        return $query->where('is_top_up', true);
    }

    public function scopeClosed($query)
    {
        return $query->whereNotNull('closure_date');
    }

    // Accessors
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'under_review' => 'bg-blue-100 text-blue-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'disbursed' => 'bg-purple-100 text-purple-800',
            'active' => 'bg-green-100 text-green-800',
            'overdue' => 'bg-red-100 text-red-800',
            'completed' => 'bg-gray-100 text-gray-800',
            'written_off' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getApprovalStatusBadgeColorAttribute(): string
    {
        return match($this->approval_status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getFormattedLoanAmountAttribute(): string
    {
        return 'TZS ' . number_format($this->loan_amount, 2);
    }

    public function getFormattedApprovedAmountAttribute(): string
    {
        return $this->approved_amount ? 'TZS ' . number_format($this->approved_amount, 2) : 'Not approved';
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return $this->total_amount ? 'TZS ' . number_format($this->total_amount, 2) : 'Not calculated';
    }

    public function getFormattedOutstandingBalanceAttribute(): string
    {
        return $this->outstanding_balance ? 'TZS ' . number_format($this->outstanding_balance, 2) : 'N/A';
    }

    public function getFormattedMonthlyPaymentAttribute(): string
    {
        return $this->monthly_payment ? 'TZS ' . number_format($this->monthly_payment, 2) : 'Not calculated';
    }

    public function getFormattedPaidAmountAttribute(): string
    {
        return 'TZS ' . number_format($this->paid_amount, 2);
    }

    public function getFormattedOverdueAmountAttribute(): string
    {
        return 'TZS ' . number_format($this->overdue_amount, 2);
    }

    public function getFormattedTopUpAmountAttribute(): string
    {
        return 'TZS ' . number_format($this->top_up_amount, 2);
    }

    public function getFormattedWriteOffAmountAttribute(): string
    {
        return 'TZS ' . number_format($this->write_off_amount, 2);
    }

    public function getProgressPercentageAttribute(): float
    {
        if (!$this->total_amount || $this->total_amount == 0) {
            return 0;
        }
        return round(($this->paid_amount / $this->total_amount) * 100, 2);
    }

    public function getDaysSinceApplicationAttribute(): int
    {
        return $this->application_date->diffInDays(now());
    }

    public function getDaysUntilMaturityAttribute(): ?int
    {
        if (!$this->maturity_date) {
            return null;
        }
        return now()->diffInDays($this->maturity_date, false);
    }

    // Static Methods
    public static function generateLoanNumber(): string
    {
        do {
            $number = 'LOAN' . date('Y') . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::where('loan_number', $number)->exists());

        return $number;
    }

    public function calculateLoanSchedule(): void
    {
        if (!$this->approved_amount || !$this->interest_rate || !$this->loan_tenure_months) {
            return;
        }

        $principal = $this->approved_amount;
        $rate = $this->interest_rate / 100;
        $months = $this->loan_tenure_months;
        $frequency = $this->repayment_frequency;

        // Calculate payment frequency multiplier
        $frequencyMultiplier = match($frequency) {
            'daily' => 30, // Approximate days in month
            'weekly' => 4, // Weeks in month
            'monthly' => 1,
            'quarterly' => 0.33, // 1/3 of a month
            default => 1,
        };

        $totalPayments = ceil($months * $frequencyMultiplier);
        
        if ($this->interest_calculation_method === 'flat') {
            $this->calculateFlatRateSchedule($principal, $rate, $totalPayments);
        } else {
            $this->calculateReducingBalanceSchedule($principal, $rate, $totalPayments);
        }

        $this->total_payments = $totalPayments;
        $this->save();
    }

    private function calculateFlatRateSchedule(float $principal, float $rate, int $totalPayments): void
    {
        $totalInterest = $principal * $rate * ($this->loan_tenure_months / 12);
        $this->total_interest = $totalInterest;
        $this->total_amount = $principal + $totalInterest;
        $this->monthly_payment = $this->total_amount / $totalPayments;

        $this->schedules()->delete();

        $paymentDate = $this->first_payment_date ?? now()->addDays(30);
        
        for ($i = 1; $i <= $totalPayments; $i++) {
            $principalAmount = $principal / $totalPayments;
            $interestAmount = $totalInterest / $totalPayments;
            
            $this->schedules()->create([
                'installment_number' => $i,
                'due_date' => $paymentDate->copy()->addMonths($i - 1),
                'principal_amount' => $principalAmount,
                'interest_amount' => $interestAmount,
                'total_amount' => $principalAmount + $interestAmount,
                'outstanding_amount' => $principalAmount + $interestAmount,
            ]);
        }
    }

    private function calculateReducingBalanceSchedule(float $principal, float $rate, int $totalPayments): void
    {
        $monthlyRate = $rate / 12;
        $this->monthly_payment = $principal * ($monthlyRate * pow(1 + $monthlyRate, $totalPayments)) / (pow(1 + $monthlyRate, $totalPayments) - 1);
        $this->total_amount = $this->monthly_payment * $totalPayments;
        $this->total_interest = $this->total_amount - $principal;

        $this->schedules()->delete();

        $paymentDate = $this->first_payment_date ?? now()->addDays(30);
        $remainingBalance = $principal;
        
        for ($i = 1; $i <= $totalPayments; $i++) {
            $interestAmount = $remainingBalance * $monthlyRate;
            $principalAmount = $this->monthly_payment - $interestAmount;
            
            // For the last payment, adjust for any rounding differences
            if ($i === $totalPayments) {
                $principalAmount = $remainingBalance;
            }
            
            $this->schedules()->create([
                'installment_number' => $i,
                'due_date' => $paymentDate->copy()->addMonths($i - 1),
                'principal_amount' => $principalAmount,
                'interest_amount' => $interestAmount,
                'total_amount' => $principalAmount + $interestAmount,
                'outstanding_amount' => $principalAmount + $interestAmount,
            ]);
            
            $remainingBalance -= $principalAmount;
        }
    }

    // Loan Operations Methods
    public function disburseLoan($disbursementAccountId, $reference = null): bool
    {
        if ($this->status !== 'approved' || !$this->approved_amount) {
            return false;
        }

        $this->status = 'disbursed';
        $this->disbursement_date = now();
        $this->disbursement_account_id = $disbursementAccountId;
        $this->disbursement_reference = $reference;
        $this->outstanding_balance = $this->approved_amount;
        if (!$this->first_payment_date) {
            $this->first_payment_date = now()->addDays(30);
        }
        $this->save();

        // Ensure a client-specific loan sub-account exists under branch Loan Portfolio
        $parentLoanPortfolioAccount = Account::where('organization_id', $this->organization_id)
            ->where('branch_id', $this->branch_id)
            ->where('name', 'like', '%Loan Portfolio%')
            ->first();

        if ($parentLoanPortfolioAccount) {
            // Try to find an existing sub-account for this loan by metadata
            $existingClientLoanAccount = Account::where('organization_id', $this->organization_id)
                ->where('branch_id', $this->branch_id)
                ->where('parent_account_id', $parentLoanPortfolioAccount->id)
                ->where('name', 'like', '%' . $this->loan_number . '%')
                ->first();

            if (!$existingClientLoanAccount) {
                Account::create([
                    'name' => ($this->client->display_name ?? 'Client') . ' - ' . $this->loan_number . ' Loan',
                    'account_number' => 'LN-' . str_pad((string)$this->id, 8, '0', STR_PAD_LEFT),
                    'account_type_id' => $parentLoanPortfolioAccount->account_type_id,
                    'parent_account_id' => $parentLoanPortfolioAccount->id,
                    'organization_id' => $this->organization_id,
                    'branch_id' => $this->branch_id,
                    'balance' => $this->approved_amount,
                    'opening_balance' => $this->approved_amount,
                    'currency' => 'TZS',
                    'description' => 'Loan receivable account for ' . ($this->client->display_name ?? 'Client') . ' (' . $this->loan_number . ')',
                    'status' => 'active',
                    'opening_date' => now(),
                    'last_transaction_date' => now(),
                    'metadata' => [
                        'client_id' => $this->client_id,
                        'loan_id' => $this->id,
                    ],
                ]);
            }
        }

        // Record disbursement transaction
        $this->transactions()->create([
            'transaction_number' => LoanTransaction::generateTransactionNumber(),
            'transaction_type' => 'disbursement',
            // Record as negative to reflect funds going out to client account
            'amount' => -1 * $this->approved_amount,
            'principal_amount' => $this->approved_amount,
            'transaction_date' => now(),
            'processed_by' => auth()->id(),
            'organization_id' => $this->organization_id,
            'branch_id' => $this->branch_id,
            'status' => 'completed',
        ]);

        // Generate repayment schedule now that loan is disbursed
        $this->calculateLoanSchedule();

        // Record in General Ledger
        $this->recordGeneralLedgerEntry('disbursement', $this->approved_amount, $disbursementAccountId);

        return true;
    }

    public function closeLoan($reason, $closedBy = null): bool
    {
        if (!in_array($this->status, ['active', 'completed'])) {
            return false;
        }

        $this->status = 'completed';
        $this->closure_date = now();
        $this->closure_reason = $reason;
        $this->closed_by = $closedBy ?? auth()->id();
        $this->save();

        // Record closure transaction
        $this->transactions()->create([
            'transaction_number' => LoanTransaction::generateTransactionNumber(),
            'transaction_type' => 'adjustment',
            'amount' => 0,
            'transaction_date' => now(),
            'processed_by' => auth()->id(),
            'organization_id' => $this->organization_id,
            'branch_id' => $this->branch_id,
            'status' => 'completed',
            'notes' => "Loan closed: {$reason}",
        ]);

        return true;
    }

    public function writeOffLoan($amount, $reason, $writtenOffBy = null): bool
    {
        if (!in_array($this->status, ['active', 'overdue'])) {
            return false;
        }

        $this->status = 'written_off';
        $this->write_off_date = now();
        $this->write_off_amount = $amount;
        $this->write_off_reason = $reason;
        $this->write_off_by = $writtenOffBy ?? auth()->id();
        $this->save();

        // Record write-off transaction
        $this->transactions()->create([
            'transaction_number' => LoanTransaction::generateTransactionNumber(),
            'transaction_type' => 'write_off',
            'amount' => $amount,
            'transaction_date' => now(),
            'processed_by' => auth()->id(),
            'organization_id' => $this->organization_id,
            'branch_id' => $this->branch_id,
            'status' => 'completed',
            'notes' => "Loan written off: {$reason}",
        ]);

        // Record in General Ledger
        $this->recordGeneralLedgerEntry('write_off', $amount);

        return true;
    }

    public function restructureLoan($newAmount, $newTenure, $newRate, $reason, $restructuredBy = null): Loan
    {
        // Create new loan record
        $newLoan = Loan::create([
            'loan_number' => Loan::generateLoanNumber(),
            'client_id' => $this->client_id,
            'loan_product_id' => $this->loan_product_id,
            'organization_id' => $this->organization_id,
            'branch_id' => $this->branch_id,
            'loan_officer_id' => $this->loan_officer_id,
            'loan_amount' => $newAmount,
            'approved_amount' => $newAmount,
            'interest_rate' => $newRate,
            'interest_calculation_method' => $this->interest_calculation_method,
            'loan_tenure_months' => $newTenure,
            'repayment_frequency' => $this->repayment_frequency,
            'application_date' => now(),
            'approval_date' => now(),
            'status' => 'approved',
            'approval_status' => 'approved',
            'approved_by' => $restructuredBy ?? auth()->id(),
            'is_restructured' => true,
            'original_loan_id' => $this->id,
            'restructure_reason' => $reason,
            'restructured_by' => $restructuredBy ?? auth()->id(),
            'restructure_date' => now(),
            'loan_purpose' => $this->loan_purpose,
            'requires_collateral' => $this->requires_collateral,
            'collateral_description' => $this->collateral_description,
            'collateral_value' => $this->collateral_value,
            'collateral_location' => $this->collateral_location,
        ]);

        // Close original loan
        $this->closeLoan("Restructured into loan {$newLoan->loan_number}: {$reason}");

        return $newLoan;
    }

    public function topUpLoan($topUpAmount, $processedBy = null): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $this->is_top_up = true;
        $this->top_up_amount = $topUpAmount;
        $this->top_up_date = now();
        $this->top_up_processed_by = $processedBy ?? auth()->id();
        
        // Update loan amounts
        $this->loan_amount += $topUpAmount;
        $this->approved_amount += $topUpAmount;
        $this->outstanding_balance += $topUpAmount;
        $this->save();

        // Record top-up transaction
        $this->transactions()->create([
            'transaction_number' => LoanTransaction::generateTransactionNumber(),
            'transaction_type' => 'disbursement',
            'amount' => $topUpAmount,
            'principal_amount' => $topUpAmount,
            'transaction_date' => now(),
            'processed_by' => auth()->id(),
            'organization_id' => $this->organization_id,
            'branch_id' => $this->branch_id,
            'status' => 'completed',
            'notes' => "Loan top-up: TZS " . number_format($topUpAmount, 2),
        ]);

        // Record in General Ledger
        $this->recordGeneralLedgerEntry('top_up', $topUpAmount);

        return true;
    }

    private function recordGeneralLedgerEntry($type, $amount, $accountId = null): void
    {
        $loanPortfolioAccount = Account::where('organization_id', $this->organization_id)
            ->where('name', 'like', '%Loan Portfolio%')
            ->where('account_type_id', function($query) {
                $query->select('id')
                    ->from('account_types')
                    ->where('name', 'Assets');
            })
            ->first();

        if (!$loanPortfolioAccount) {
            return; // Skip if loan portfolio account doesn't exist
        }

        $description = match($type) {
            'disbursement' => "Loan disbursement for {$this->loan_number}",
            'write_off' => "Loan write-off for {$this->loan_number}",
            'top_up' => "Loan top-up for {$this->loan_number}",
            default => "Loan transaction for {$this->loan_number}",
        };

        // Debit Loan Portfolio (Asset)
        GeneralLedger::create([
            'transaction_number' => 'GL' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'account_id' => $loanPortfolioAccount->id,
            'organization_id' => $this->organization_id,
            'branch_id' => $this->branch_id,
            'transaction_type' => $type === 'write_off' ? 'credit' : 'debit',
            'amount' => $amount,
            'description' => $description,
            'reference_type' => 'App\\Models\\Loan',
            'reference_id' => $this->id,
            'transaction_date' => now(),
            'created_by' => auth()->id(),
        ]);

        // Credit Source Account (Liability) - for disbursements
        if ($type === 'disbursement' && $accountId) {
            GeneralLedger::create([
                'transaction_number' => 'GL' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'account_id' => $accountId,
                'organization_id' => $this->organization_id,
                'branch_id' => $this->branch_id,
                'transaction_type' => 'credit',
                'amount' => $amount,
                'description' => $description,
                'reference_type' => 'App\\Models\\Loan',
                'reference_id' => $this->id,
                'transaction_date' => now(),
                'created_by' => auth()->id(),
            ]);
        }
    }
}
