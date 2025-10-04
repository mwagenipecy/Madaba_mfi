<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundTransfer extends Model
{
    protected $fillable = [
        'transfer_number',
        'from_account_id',
        'to_account_id',
        'amount',
        'currency',
        'description',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'completed_at',
        'rejection_reason',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the source account
     */
    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    /**
     * Get the destination account
     */
    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    /**
     * Get the user who requested the transfer
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved the transfer
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the approvals for this transfer
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class, 'reference_id')->where('reference_type', 'FundTransfer');
    }

    /**
     * Scope to get pending transfers
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get approved transfers
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get completed transfers
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get transfers by requester
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
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    /**
     * Check if transfer can be approved
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transfer can be completed
     */
    public function canBeCompleted(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Approve the transfer
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
            'type' => 'fund_transfer',
            'reference_type' => 'FundTransfer',
            'reference_id' => $this->id,
            'requested_by' => $this->requested_by,
            'approver_id' => $approvedBy,
            'status' => 'approved',
            'description' => "Approval for fund transfer: {$this->transfer_number}",
            'approval_notes' => $notes,
            'approved_at' => now(),
        ]);

        return true;
    }

    /**
     * Complete the transfer
     */
    public function complete(): bool
    {
        if (!$this->canBeCompleted()) {
            return false;
        }

        // Use the AccountingService to create proper accounting entries
        $accountingService = app(\App\Services\AccountingService::class);
        $accountingService->recordFundTransfer($this);

        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return true;
    }

    /**
     * Reject the transfer
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
