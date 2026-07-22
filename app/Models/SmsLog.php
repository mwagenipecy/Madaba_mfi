<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLog extends Model
{
    protected $fillable = [
        'batch_id',
        'organization_id',
        'branch_id',
        'client_id',
        'loan_id',
        'sent_by',
        'recipient',
        'sender_id',
        'content',
        'criteria',
        'source',
        'status',
        'job_id',
        'provider_status',
        'cost',
        'error_message',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'cost' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'sent' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            default => 'bg-yellow-100 text-yellow-800',
        };
    }
}
