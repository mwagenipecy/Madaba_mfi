<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'installment_number',
        'due_date',
        'principal_amount',
        'interest_amount',
        'total_amount',
        'status',
        'paid_amount',
        'paid_principal_amount',
        'paid_interest_amount',
        'outstanding_amount',
        'paid_date',
        'days_overdue',
        'late_fee',
        'penalty_fee',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid_principal_amount' => 'decimal:2',
        'paid_interest_amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'penalty_fee' => 'decimal:2',
        'days_overdue' => 'integer',
        'installment_number' => 'integer',
    ];

    // Relationships
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoanTransaction::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeDueToday($query)
    {
        return $query->where('due_date', today());
    }

    public function scopeDueSoon($query, $days = 7)
    {
        return $query->where('due_date', '<=', now()->addDays($days))
                    ->where('status', 'pending');
    }

    // Accessors
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'paid' => 'bg-green-100 text-green-800',
            'overdue' => 'bg-red-100 text-red-800',
            'partial' => 'bg-orange-100 text-orange-800',
            'waived' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getFormattedPrincipalAmountAttribute(): string
    {
        return 'TZS ' . number_format($this->principal_amount, 2);
    }

    public function getFormattedInterestAmountAttribute(): string
    {
        return 'TZS ' . number_format($this->interest_amount, 2);
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return 'TZS ' . number_format($this->total_amount, 2);
    }

    public function getFormattedPaidAmountAttribute(): string
    {
        return 'TZS ' . number_format($this->paid_amount, 2);
    }

    public function getFormattedOutstandingAmountAttribute(): string
    {
        return 'TZS ' . number_format($this->outstanding_amount, 2);
    }

    public function getRemainingPrincipalAttribute(): float
    {
        return max(0, round((float) $this->principal_amount - (float) ($this->paid_principal_amount ?? 0), 2));
    }

    public function getRemainingInterestAttribute(): float
    {
        return max(0, round((float) $this->interest_amount - (float) ($this->paid_interest_amount ?? 0), 2));
    }

    public function getRemainingTotalAttribute(): float
    {
        return round($this->remaining_principal + $this->remaining_interest, 2);
    }

    public function getIsDueReachedAttribute(): bool
    {
        return $this->due_date->lte(today());
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date < today() && $this->status !== 'paid';
    }

    public function getDaysOverdueAttribute(): int
    {
        if ($this->due_date >= today() || $this->status === 'paid') {
            return 0;
        }
        return $this->due_date->diffInDays(today());
    }

    // Methods
    public function applyPayment(float $principalPaid, float $interestPaid): void
    {
        $this->paid_principal_amount = round((float) ($this->paid_principal_amount ?? 0) + $principalPaid, 2);
        $this->paid_interest_amount = round((float) ($this->paid_interest_amount ?? 0) + $interestPaid, 2);
        $this->paid_amount = round($this->paid_principal_amount + $this->paid_interest_amount, 2);
        $this->outstanding_amount = $this->remaining_total;

        $this->refreshStatus();
        $this->save();
    }

    public function refreshStatus(): void
    {
        if ($this->remaining_total <= 0.01) {
            $this->status = 'paid';
            $this->paid_date = $this->paid_date ?? now();
            $this->outstanding_amount = 0;
            $this->days_overdue = 0;
            return;
        }

        if ($this->paid_amount > 0) {
            $this->status = 'partial';
            return;
        }

        if ($this->due_date->lt(today())) {
            $this->status = 'overdue';
            $this->days_overdue = $this->due_date->diffInDays(today());
            return;
        }

        $this->status = 'pending';
        $this->days_overdue = 0;
    }

    public function markAsPaid($amount = null, $date = null): void
    {
        $this->paid_principal_amount = $this->principal_amount;
        $this->paid_interest_amount = $this->interest_amount;
        $this->paid_amount = $amount ?? $this->total_amount;
        $this->outstanding_amount = 0;
        $this->status = 'paid';
        $this->paid_date = $date ?? now();
        $this->save();
    }

    public function markAsOverdue(): void
    {
        $this->refreshStatus();
        $this->save();
    }
}
