<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $table = 'otp_verifications';

    protected $fillable = [
        'email',
        'otp_code',
        'action',
        'expires_at',
        'is_used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used'    => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope valid OTP (matches code, not used, not expired)
     */
    public function scopeValid($query, string $email, string $otp, string $action = 'registration')
    {
        return $query->where('email', $email)
            ->where('otp_code', $otp)
            ->where('action', $action)
            ->where('is_used', false)
            ->where('expires_at', '>=', now());
    }
}

