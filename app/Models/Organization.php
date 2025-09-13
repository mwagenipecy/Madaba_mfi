<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'registration_number',
        'license_number',
        'type',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'authorized_capital',
        'incorporation_date',
        'regulatory_info',
        'logo_path',
        'status',
        'description',
        'settings',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'regulatory_info' => 'array',
        'settings' => 'array',
        'authorized_capital' => 'decimal:2',
        'incorporation_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($organization) {
            if (empty($organization->slug)) {
                $organization->slug = Str::slug($organization->name);
            }
        });
    }

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function admins()
    {
        return $this->users()->whereIn('role', ['super_admin', 'admin']);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Accessors
    public function getFullAddressAttribute()
    {
        return "{$this->address}, {$this->city}, {$this->state}, {$this->country}";
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Methods
    public function activate()
    {
        $this->update([
            'status' => 'active',
            'approved_at' => now(),
        ]);
    }

    public function suspend()
    {
        $this->update(['status' => 'suspended']);
    }
}