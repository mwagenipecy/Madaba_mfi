<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RealAccount extends Model
{
    protected $fillable = [
        'account_id',
        'provider_type',
        'provider_name',
        'external_account_id',
        'external_account_name',
        'api_endpoint',
        'api_credentials',
        'last_balance',
        'last_sync_at',
        'sync_status',
        'sync_error_message',
        'provider_metadata',
        'is_active',
    ];

    protected $casts = [
        'api_credentials' => 'array',
        'last_balance' => 'decimal:2',
        'last_sync_at' => 'datetime',
        'provider_metadata' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the account that owns the real account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the accounts that are mapped to this real account
     */
    public function mappedAccounts(): HasMany
    {
        return $this->hasMany(Account::class, 'real_account_id');
    }

    /**
     * Scope to get active real accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get real accounts by provider type
     */
    public function scopeByProviderType($query, $providerType)
    {
        return $query->where('provider_type', $providerType);
    }

    /**
     * Scope to get MNO accounts
     */
    public function scopeMno($query)
    {
        return $query->where('provider_type', 'mno');
    }

    /**
     * Scope to get Bank accounts
     */
    public function scopeBank($query)
    {
        return $query->where('provider_type', 'bank');
    }

    /**
     * Get the provider type badge color
     */
    public function getProviderTypeBadgeColorAttribute(): string
    {
        return match($this->provider_type) {
            'mno' => 'bg-blue-100 text-blue-800',
            'bank' => 'bg-green-100 text-green-800',
            'payment_gateway' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the sync status badge color
     */
    public function getSyncStatusBadgeColorAttribute(): string
    {
        return match($this->sync_status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'success' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'disabled' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get formatted last balance
     */
    public function getFormattedLastBalanceAttribute(): string
    {
        return number_format($this->last_balance, 2);
    }

    /**
     * Check if sync is needed (last sync was more than 1 hour ago)
     */
    public function getNeedsSyncAttribute(): bool
    {
        if (!$this->last_sync_at) {
            return true;
        }

        return $this->last_sync_at->diffInHours(now()) >= 1;
    }
}
