<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'loan_schedule_id',
        'transaction_number',
        'transaction_type',
        'amount',
        'payment_method',
        'reference_number',
        'transaction_date',
        'transaction_time',
        'principal_amount',
        'interest_amount',
        'fee_amount',
        'penalty_amount',
        'status',
        'notes',
        'failure_reason',
        'processed_by',
        'organization_id',
        'branch_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'transaction_date' => 'date',
        'transaction_time' => 'datetime:H:i:s',
    ];

    // Relationships
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function loanSchedule(): BelongsTo
    {
        return $this->belongsTo(LoanSchedule::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    // Accessors
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getTransactionTypeBadgeColorAttribute(): string
    {
        return match($this->transaction_type) {
            'disbursement' => 'bg-blue-100 text-blue-800',
            'principal_payment' => 'bg-green-100 text-green-800',
            'interest_payment' => 'bg-purple-100 text-purple-800',
            'late_fee' => 'bg-red-100 text-red-800',
            'penalty_fee' => 'bg-red-100 text-red-800',
            'processing_fee' => 'bg-orange-100 text-orange-800',
            'insurance_fee' => 'bg-indigo-100 text-indigo-800',
            'refund' => 'bg-cyan-100 text-cyan-800',
            'adjustment' => 'bg-yellow-100 text-yellow-800',
            'write_off' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'TZS ' . number_format($this->amount, 2);
    }

    public function getFormattedPrincipalAmountAttribute(): string
    {
        return 'TZS ' . number_format($this->principal_amount, 2);
    }

    public function getFormattedInterestAmountAttribute(): string
    {
        return 'TZS ' . number_format($this->interest_amount, 2);
    }

    public function getFormattedFeeAmountAttribute(): string
    {
        return 'TZS ' . number_format($this->fee_amount, 2);
    }

    public function getFormattedPenaltyAmountAttribute(): string
    {
        return 'TZS ' . number_format($this->penalty_amount, 2);
    }

    public function getTransactionDateTimeAttribute(): string
    {
        return $this->transaction_date->format('M d, Y') . ' ' . ($this->transaction_time ? $this->transaction_time->format('H:i:s') : '');
    }

    // Static Methods
    public static function generateTransactionNumber(): string
    {
        do {
            $number = 'TXN' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('transaction_number', $number)->exists());

        return $number;
    }
}
