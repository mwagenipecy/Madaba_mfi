<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Collateral extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'land' => 'Land',
        'vehicle' => 'Vehicle',
        'equipment' => 'Equipment',
        'livestock' => 'Livestock',
        'property' => 'Property',
        'savings' => 'Savings / Fixed Deposit',
        'other' => 'Other',
    ];

    protected $fillable = [
        'uuid',
        'reference_number',
        'organization_id',
        'branch_id',
        'client_id',
        'loan_id',
        'created_by',
        'type',
        'title',
        'description',
        'estimated_value',
        'lending_ratio',
        'location',
        'identifier',
        'document_path',
        'status',
        'pledged_at',
        'released_at',
        'metadata',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'lending_ratio' => 'decimal:2',
        'pledged_at' => 'datetime',
        'released_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Collateral $collateral) {
            if (empty($collateral->uuid)) {
                $collateral->uuid = (string) Str::uuid();
            }
            if (empty($collateral->reference_number)) {
                $collateral->reference_number = static::generateReferenceNumber();
            }
        });
    }

    public static function generateReferenceNumber(): string
    {
        do {
            $number = 'COL-' . now()->format('ym') . '-' . strtoupper(Str::random(6));
        } while (static::where('reference_number', $number)->exists());

        return $number;
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lendingCapacity(): float
    {
        return round((float) $this->estimated_value * ((float) $this->lending_ratio / 100), 2);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->loan_id === null;
    }

    public function pledgeToLoan(Loan $loan): void
    {
        $this->update([
            'status' => 'pledged',
            'loan_id' => $loan->id,
            'pledged_at' => now(),
            'released_at' => null,
        ]);
    }

    public function releaseFromLoan(): void
    {
        $this->update([
            'status' => 'available',
            'loan_id' => null,
            'released_at' => now(),
        ]);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pledged' => 'Pledged to loan',
            'released' => 'Released',
            default => 'Available',
        };
    }
}
