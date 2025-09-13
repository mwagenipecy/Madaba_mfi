<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'category',
        'balance_type',
        'is_main_account',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_main_account' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the accounts for this account type
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Scope to get active account types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get main account types
     */
    public function scopeMainAccounts($query)
    {
        return $query->where('is_main_account', true);
    }

    /**
     * Scope to get account types by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get the category badge color
     */
    public function getCategoryBadgeColorAttribute(): string
    {
        return match($this->category) {
            'asset' => 'bg-green-100 text-green-800',
            'liability' => 'bg-red-100 text-red-800',
            'equity' => 'bg-blue-100 text-blue-800',
            'income' => 'bg-yellow-100 text-yellow-800',
            'expense' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
