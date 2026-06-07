<?php

namespace App\Repositories\Auth;

use App\Models\EmailOtp;
use Illuminate\Support\Facades\Hash;

class EmailOtpRepository
{
    public function createOrUpdate(
        string $email,
        string $otp,
        $expiresAt
    ): EmailOtp {

        return EmailOtp::updateOrCreate(
            [
                'email' => $email
            ],
            [
                'otp' => Hash::make($otp),

                'attempts' => 0,

                'verified_at' => null,

                'expires_at' => $expiresAt,
            ]
        );
    }

    public function findValidOtp(
        string $email,
        string $otp
    ): ?EmailOtp {
        return EmailOtp::where('email', $email)
            ->where('otp', $otp)
            ->first();
    }

    public function deleteByEmail(
        string $email
    ): void {
        EmailOtp::where('email', $email)
            ->delete();
    }

    public function findByEmail(
        string $email
    ): ?EmailOtp {

        return EmailOtp::where(
            'email',
            $email
        )->first();
    }

    public function incrementAttempts(
        EmailOtp $otp
    ): void {

        $otp->increment('attempts');
    }
    public function markVerified(
        EmailOtp $otp
    ): void {

        $otp->update([
            'verified_at' => now()
        ]);
    }
}