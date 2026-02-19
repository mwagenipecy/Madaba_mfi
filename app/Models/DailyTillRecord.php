<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyTillRecord extends Model
{
    protected $fillable = [
        'organization_id',
        'branch_id',
        'record_date',
        'opening_cash',
        'opening_mobile_wallet',
        'closing_cash',
        'closing_mobile_wallet',
        'amount_collected',
        'loans_given',
        'expenses',
        'opening_recorded_by',
        'opening_recorded_at',
        'closing_recorded_by',
        'closing_recorded_at',
        'opening_notes',
        'closing_notes',
    ];

    protected $casts = [
        'record_date' => 'date',
        'opening_cash' => 'decimal:2',
        'opening_mobile_wallet' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'closing_mobile_wallet' => 'decimal:2',
        'amount_collected' => 'decimal:2',
        'loans_given' => 'decimal:2',
        'expenses' => 'array',
        'opening_recorded_at' => 'datetime',
        'closing_recorded_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function openingRecordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opening_recorded_by');
    }

    public function closingRecordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closing_recorded_by');
    }
}
