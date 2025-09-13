<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'organization_id',
        'min_amount',
        'max_amount',
        'interest_rate',
        'interest_type',
        'interest_calculation_method',
        'min_tenure_months',
        'max_tenure_months',
        'processing_fee',
        'late_fee',
        'repayment_frequency',
        'grace_period_days',
        'eligibility_criteria',
        'required_documents',
        'requires_collateral',
        'collateral_ratio',
        'status',
        'is_featured',
        'sort_order',
        'metadata',
        'disbursement_account_id',
        'collection_account_id',
        'interest_revenue_account_id',
        'principal_account_id',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'collateral_ratio' => 'decimal:2',
        'eligibility_criteria' => 'array',
        'required_documents' => 'array',
        'requires_collateral' => 'boolean',
        'is_featured' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the organization that owns the loan product
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the disbursement account
     */
    public function disbursementAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'disbursement_account_id');
    }

    /**
     * Get the collection account
     */
    public function collectionAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'collection_account_id');
    }

    /**
     * Get the interest revenue account
     */
    public function interestRevenueAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'interest_revenue_account_id');
    }

    /**
     * Get the principal account
     */
    public function principalAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'principal_account_id');
    }

    /**
     * Scope to get active loan products
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get featured loan products
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to get loan products by organization
     */
    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Get the status badge color
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-gray-100 text-gray-800',
            'suspended' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the interest type badge color
     */
    public function getInterestTypeBadgeColorAttribute(): string
    {
        return match($this->interest_type) {
            'fixed' => 'bg-blue-100 text-blue-800',
            'variable' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the interest calculation method badge color
     */
    public function getInterestCalculationMethodBadgeColorAttribute(): string
    {
        return match($this->interest_calculation_method) {
            'flat' => 'bg-green-100 text-green-800',
            'reducing' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the repayment frequency badge color
     */
    public function getRepaymentFrequencyBadgeColorAttribute(): string
    {
        return match($this->repayment_frequency) {
            'daily' => 'bg-red-100 text-red-800',
            'weekly' => 'bg-orange-100 text-orange-800',
            'monthly' => 'bg-green-100 text-green-800',
            'quarterly' => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get formatted minimum amount
     */
    public function getFormattedMinAmountAttribute(): string
    {
        return '$' . number_format($this->min_amount, 2);
    }

    /**
     * Get formatted maximum amount
     */
    public function getFormattedMaxAmountAttribute(): string
    {
        return '$' . number_format($this->max_amount, 2);
    }

    /**
     * Get formatted interest rate
     */
    public function getFormattedInterestRateAttribute(): string
    {
        return $this->interest_rate . '%';
    }

    /**
     * Get formatted tenure range
     */
    public function getFormattedTenureRangeAttribute(): string
    {
        if ($this->min_tenure_months === $this->max_tenure_months) {
            return $this->min_tenure_months . ' months';
        }
        return $this->min_tenure_months . ' - ' . $this->max_tenure_months . ' months';
    }

    /**
     * Check if product is active
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if product requires collateral
     */
    public function getRequiresCollateralAttribute(): bool
    {
        return (bool) $this->getRawOriginal('requires_collateral');
    }
}