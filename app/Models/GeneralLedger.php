<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GeneralLedger extends Model
{
    protected $table = 'general_ledger';

    protected $fillable = [
        'organization_id',
        'branch_id',
        'transaction_id',
        'transaction_date',
        'account_id',
        'transaction_type',
        'amount',
        'currency',
        'description',
        'reference_type',
        'reference_id',
        'created_by',
        'approved_by',
        'approved_at',
        'balance_after',
        'metadata',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'approved_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the organization that owns the ledger entry
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the branch that owns the ledger entry
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the account that owns the ledger entry
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the user who created the transaction
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the transaction
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the reference model (polymorphic)
     */
    public function reference(): MorphTo
    {
        return $this->morphTo('reference');
    }

    /**
     * Scope to get debit transactions
     */
    public function scopeDebits($query)
    {
        return $query->where('transaction_type', 'debit');
    }

    /**
     * Scope to get credit transactions
     */
    public function scopeCredits($query)
    {
        return $query->where('transaction_type', 'credit');
    }

    /**
     * Scope to get transactions by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get transactions by account
     */
    public function scopeByAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Scope to get transactions by organization
     */
    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope to get transactions by branch
     */
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Get the transaction type badge color
     */
    public function getTransactionTypeBadgeColorAttribute(): string
    {
        return match($this->transaction_type) {
            'debit' => 'bg-red-100 text-red-800',
            'credit' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->transaction_type === 'debit' ? '-' : '+';
        return $prefix . number_format($this->amount, 2) . ' ' . $this->currency;
    }

    /**
     * Get formatted balance after
     */
    public function getFormattedBalanceAfterAttribute(): string
    {
        return number_format($this->balance_after, 2) . ' ' . $this->currency;
    }

    /**
     * Static method to create ledger entries for a transaction
     */
    public static function createTransaction(
        string $transactionId,
        Account $account,
        string $transactionType,
        float $amount,
        string $description,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $approvedBy = null
    ): self {
        // Calculate new balance based on account type and transaction type
        $accountType = $account->accountType;
        $currentBalance = $account->current_balance ?? $account->balance;
        
        // For assets and expenses: debit increases, credit decreases
        // For liabilities, equity, and income: debit decreases, credit increases
        if (in_array($accountType->category, ['asset', 'expense'])) {
            $newBalance = $transactionType === 'debit' 
                ? $currentBalance + $amount 
                : $currentBalance - $amount;
        } else {
            // For liability, equity, income accounts
            $newBalance = $transactionType === 'credit' 
                ? $currentBalance + $amount 
                : $currentBalance - $amount;
        }

        $ledgerEntry = self::create([
            'organization_id' => $account->organization_id,
            'branch_id' => $account->branch_id,
            'transaction_id' => $transactionId,
            'transaction_date' => now()->toDateString(),
            'account_id' => $account->id,
            'transaction_type' => $transactionType,
            'amount' => $amount,
            'currency' => $account->currency ?? 'TZS',
            'description' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'created_by' => auth()->id(),
            'approved_by' => $approvedBy,
            'approved_at' => $approvedBy ? now() : null,
            'balance_after' => $newBalance,
        ]);

        // Update account balance
        $account->update([
            'current_balance' => $newBalance,
            'last_transaction_date' => now()
        ]);

        return $ledgerEntry;
    }
}
