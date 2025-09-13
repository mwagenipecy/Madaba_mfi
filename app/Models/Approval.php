<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Approval extends Model
{
    protected $fillable = [
        'approval_number',
        'organization_id',
        'branch_id',
        'type',
        'reference_type',
        'reference_id',
        'requested_by',
        'approver_id',
        'status',
        'description',
        'approval_notes',
        'approved_at',
        'metadata',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Generate unique approval number
     */
    public static function generateApprovalNumber(): string
    {
        $prefix = 'APR';
        $date = date('Ymd');
        $lastApproval = self::where('approval_number', 'like', $prefix . $date . '%')
            ->orderBy('approval_number', 'desc')
            ->first();
        
        $sequence = $lastApproval ? 
            (int) substr($lastApproval->approval_number, -4) + 1 : 1;
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the user who requested the approval
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who should approve
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Get the organization
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the reference model (polymorphic)
     */
    public function reference(): MorphTo
    {
        return $this->morphTo('reference');
    }

    /**
     * Scope to get pending approvals
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get approved items
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get rejected items
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope to get approvals by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get approvals for a specific approver
     */
    public function scopeForApprover($query, $approverId)
    {
        return $query->where('approver_id', $approverId);
    }

    /**
     * Get the status badge color
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the type badge color
     */
    public function getTypeBadgeColorAttribute(): string
    {
        return match($this->type) {
            'fund_transfer' => 'bg-blue-100 text-blue-800',
            'account_recharge' => 'bg-purple-100 text-purple-800',
            'other' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Check if approval can be processed
     */
    public function canBeProcessed(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Approve the request
     */
    public function approve($notes = null): bool
    {
        if (!$this->canBeProcessed()) {
            return false;
        }

        $this->update([
            'status' => 'approved',
            'approval_notes' => $notes,
            'approved_at' => now(),
        ]);

        // Process the reference item based on type
        $this->processApproval();

        return true;
    }

    /**
     * Reject the request
     */
    public function reject($notes = null): bool
    {
        if (!$this->canBeProcessed()) {
            return false;
        }

        $this->update([
            'status' => 'rejected',
            'approval_notes' => $notes,
            'approved_at' => now(),
        ]);

        return true;
    }

    /**
     * Process the approval based on type
     */
    private function processApproval(): void
    {
        switch ($this->type) {
            case 'fund_transfer':
                $fundTransfer = FundTransfer::find($this->reference_id);
                if ($fundTransfer) {
                    $fundTransfer->approve($this->approver_id, $this->approval_notes);
                }
                break;
            case 'account_recharge':
                $recharge = AccountRecharge::find($this->reference_id);
                if ($recharge) {
                    $recharge->approve($this->approver_id, $this->approval_notes);
                }
                break;
        }
    }
}
