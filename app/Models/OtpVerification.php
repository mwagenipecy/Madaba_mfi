<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'otp_code',
        'expires_at',
        'is_used',
        'used_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Get the user that owns the OTP verification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the OTP is expired.
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the OTP is valid (not used and not expired).
     */
    public function isValid()
    {
        return !$this->is_used && !$this->isExpired();
    }

    /**
     * Mark the OTP as used.
     */
    public function markAsUsed()
    {
        $this->update([
            'is_used' => true,
            'used_at' => now(),
        ]);
    }

    /**
     * Generate a new OTP for the user.
     */
    public static function generateForUser($userId, $ipAddress = null, $userAgent = null)
    {
        // For now, always use 111111 as requested
        $otpCode = '111111';
        
        // Expire in 10 minutes
        $expiresAt = now()->addMinutes(10);

        // Invalidate any existing OTPs for this user
        static::where('user_id', $userId)
            ->where('is_used', false)
            ->update(['is_used' => true, 'used_at' => now()]);

        return static::create([
            'user_id' => $userId,
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Verify OTP code for a user.
     */
    public static function verifyForUser($userId, $otpCode)
    {
        $otp = static::where('user_id', $userId)
            ->where('otp_code', $otpCode)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($otp) {
            $otp->markAsUsed();
            return true;
        }

        return false;
    }
}