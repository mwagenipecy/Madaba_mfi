<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_number',
        'client_type',
        'organization_id',
        'branch_id',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'national_id',
        'passport_number',
        'business_name',
        'business_registration_number',
        'business_type',
        'phone_number',
        'secondary_phone',
        'email',
        'physical_address',
        'city',
        'region',
        'country',
        'postal_code',
        'kyc_status',
        'kyc_verification_date',
        'verified_by',
        'kyc_notes',
        'monthly_income',
        'income_source',
        'employer_name',
        'employment_address',
        'bank_name',
        'bank_account_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'documents',
        'kyc_documents',
        'marital_status',
        'dependents',
        'occupation',
        'business_description',
        'years_in_business',
        'annual_turnover',
        'status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'kyc_verification_date' => 'date',
        'monthly_income' => 'decimal:2',
        'annual_turnover' => 'decimal:2',
        'documents' => 'array',
        'kyc_documents' => 'array',
        'metadata' => 'array',
        'dependents' => 'integer',
        'years_in_business' => 'integer',
    ];

    // Relationships
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeIndividual($query)
    {
        return $query->where('client_type', 'individual');
    }

    public function scopeBusiness($query)
    {
        return $query->whereIn('client_type', ['group', 'business']);
    }

    public function scopeVerified($query)
    {
        return $query->where('kyc_status', 'verified');
    }

    public function scopePendingKyc($query)
    {
        return $query->where('kyc_status', 'pending');
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        if ($this->client_type === 'individual') {
            return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
        }
        return $this->business_name ?? 'N/A';
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->client_type === 'individual') {
            return $this->full_name;
        }
        return $this->business_name ?? 'N/A';
    }

    public function getKycStatusBadgeColorAttribute(): string
    {
        return match($this->kyc_status) {
            'verified' => 'bg-green-100 text-green-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'rejected' => 'bg-red-100 text-red-800',
            'expired' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-gray-100 text-gray-800',
            'suspended' => 'bg-yellow-100 text-yellow-800',
            'blacklisted' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getClientTypeBadgeColorAttribute(): string
    {
        return match($this->client_type) {
            'individual' => 'bg-blue-100 text-blue-800',
            'business' => 'bg-purple-100 text-purple-800',
            'group' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getFormattedMonthlyIncomeAttribute(): string
    {
        return $this->monthly_income ? 'TZS ' . number_format($this->monthly_income, 2) : 'N/A';
    }

    public function getFormattedAnnualTurnoverAttribute(): string
    {
        return $this->annual_turnover ? 'TZS ' . number_format($this->annual_turnover, 2) : 'N/A';
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    // Static Methods
    public static function generateClientNumber(): string
    {
        do {
            $number = 'CLI' . date('Y') . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::where('client_number', $number)->exists());

        return $number;
    }
}
