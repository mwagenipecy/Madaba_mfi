<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'account_number',
        'account_type_id',
        'parent_account_id',
        'organization_id',
        'branch_id',
        'real_account_id',
        'mapping_description',
        'balance',
        'opening_balance',
        'currency',
        'description',
        'status',
        'opening_date',
        'last_transaction_date',
        'metadata',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'opening_balance' => 'decimal:2',
        'opening_date' => 'datetime',
        'last_transaction_date' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the account type that owns the account
     */
    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }

    /**
     * Get the organization that owns the account
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the branch that owns the account
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the real accounts for this account
     */
    public function realAccounts(): HasMany
    {
        return $this->hasMany(RealAccount::class);
    }

    /**
     * Backward-compatible singular real account relationship
     */
    public function realAccount(): HasOne
    {
        return $this->hasOne(RealAccount::class);
    }

    /**
     * Get the mapped real account for this account
     */
    public function mappedRealAccount(): BelongsTo
    {
        return $this->belongsTo(RealAccount::class, 'real_account_id');
    }

    /**
     * Get the parent account (for sub-accounts)
     */
    public function parentAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    /**
     * Get the child accounts (sub-accounts)
     */
    public function childAccounts(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_account_id');
    }

    /**
     * Scope to get active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get main organization accounts (no branch)
     */
    public function scopeMainAccounts($query)
    {
        return $query->whereNull('branch_id');
    }

    /**
     * Scope to get branch-specific accounts
     */
    public function scopeBranchAccounts($query)
    {
        return $query->whereNotNull('branch_id');
    }

    /**
     * Scope to get accounts by organization
     */
    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope to get accounts by branch
     */
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to get main category accounts (parent accounts)
     */
    public function scopeMainCategories($query)
    {
        return $query->whereNull('parent_account_id');
    }

    /**
     * Scope to get sub-accounts
     */
    public function scopeSubAccounts($query)
    {
        return $query->whereNotNull('parent_account_id');
    }

    /**
     * Scope to get accounts with mapped real accounts
     */
    public function scopeWithMappedAccounts($query)
    {
        return $query->whereNotNull('real_account_id');
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
            'closed' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Check if this is a main account (no branch)
     */
    public function getIsMainAccountAttribute(): bool
    {
        return is_null($this->branch_id);
    }

    /**
     * Check if this is a branch account
     */
    public function getIsBranchAccountAttribute(): bool
    {
        return !is_null($this->branch_id);
    }

    /**
     * Get formatted balance
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 2) . ' ' . $this->currency;
    }

    /**
     * Check if this is a main category account
     */
    public function getIsMainCategoryAttribute(): bool
    {
        return is_null($this->parent_account_id);
    }

    /**
     * Check if this is a sub-account
     */
    public function getIsSubAccountAttribute(): bool
    {
        return !is_null($this->parent_account_id);
    }

    /**
     * Check if this account has a mapped real account
     */
    public function getHasMappedAccountAttribute(): bool
    {
        return !is_null($this->real_account_id);
    }

    /**
     * Get the mapping status badge color
     */
    public function getMappingStatusBadgeColorAttribute(): string
    {
        if ($this->has_mapped_account) {
            return 'bg-green-100 text-green-800';
        }
        
        return 'bg-gray-100 text-gray-800';
    }

    /**
     * Get mapping status text
     */
    public function getMappingStatusAttribute(): string
    {
        if ($this->has_mapped_account) {
            return 'Mapped to Real Account';
        }
        
        return 'Not Mapped';
    }
}
