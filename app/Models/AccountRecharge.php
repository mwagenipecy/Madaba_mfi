<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountRecharge extends Model
{
    protected $fillable = [
        'recharge_number',
        'main_account_id',
        'recharge_amount',
        'currency',
        'description',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'completed_at',
        'rejection_reason',
        'distribution_plan',
        'metadata',
    ];

    protected $casts = [
        'recharge_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'distribution_plan' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the main account being recharged
     */
    public function mainAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'main_account_id');
    }

    /**
     * Get the user who requested the recharge
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved the recharge
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the approvals for this recharge
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class, 'reference_id')->where('reference_type', 'AccountRecharge');
    }

    /**
     * Scope to get pending recharges
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get approved recharges
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get completed recharges
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get recharges by requester
     */
    public function scopeByRequester($query, $userId)
    {
        return $query->where('requested_by', $userId);
    }

    /**
     * Get the status badge color
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get formatted recharge amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->recharge_amount, 2) . ' ' . $this->currency;
    }

    /**
     * Check if recharge can be approved
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if recharge can be completed
     */
    public function canBeCompleted(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Approve the recharge
     */
    public function approve($approvedBy, $notes = null): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }

        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        // Create approval record
        Approval::create([
            'approval_number' => 'APP-' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'type' => 'account_recharge',
            'reference_type' => 'AccountRecharge',
            'reference_id' => $this->id,
            'requested_by' => $this->requested_by,
            'approver_id' => $approvedBy,
            'status' => 'approved',
            'description' => "Approval for account recharge: {$this->recharge_number}",
            'approval_notes' => $notes,
            'approved_at' => now(),
        ]);

        return true;
    }

    /**
     * Complete the recharge and distribute funds
     */
    public function complete(): bool
    {
        if (!$this->canBeCompleted()) {
            return false;
        }

        // First, credit the main account
        $transactionId = 'RC-' . $this->recharge_number;

        GeneralLedger::createTransaction(
            $transactionId . '-MAIN',
            $this->mainAccount,
            'credit',
            $this->recharge_amount,
            "Account recharge: {$this->description}",
            'AccountRecharge',
            $this->id,
            $this->approved_by
        );

        // Then distribute to branch accounts if distribution plan exists
        if ($this->distribution_plan && is_array($this->distribution_plan)) {
            foreach ($this->distribution_plan as $distribution) {
                $branchAccount = Account::find($distribution['account_id']);
                if ($branchAccount && $distribution['amount'] > 0) {
                    // Credit branch account
                    GeneralLedger::createTransaction(
                        $transactionId . '-DIST-' . $branchAccount->id,
                        $branchAccount,
                        'credit',
                        $distribution['amount'],
                        "Distribution from recharge: {$this->recharge_number}",
                        'AccountRecharge',
                        $this->id,
                        $this->approved_by
                    );

                    // Debit main account for distribution
                    GeneralLedger::createTransaction(
                        $transactionId . '-DIST-DEBIT-' . $branchAccount->id,
                        $this->mainAccount,
                        'debit',
                        $distribution['amount'],
                        "Distribution to {$branchAccount->name}",
                        'AccountRecharge',
                        $this->id,
                        $this->approved_by
                    );
                }
            }
        }

        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return true;
    }

    /**
     * Reject the recharge
     */
    public function reject($rejectedBy, $reason): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }

        $this->update([
            'status' => 'rejected',
            'approved_by' => $rejectedBy,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return true;
    }
}
