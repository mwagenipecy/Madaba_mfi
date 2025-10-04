<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountRealAccountMapping extends Model
{
    protected $table = 'account_real_account_mappings';
    
    protected $fillable = [
        'account_id',
        'real_account_id',
        'mapping_description',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the account that owns the mapping
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the real account that owns the mapping
     */
    public function realAccount(): BelongsTo
    {
        return $this->belongsTo(RealAccount::class);
    }
}
