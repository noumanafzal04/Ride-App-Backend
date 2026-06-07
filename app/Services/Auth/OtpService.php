<?php

namespace App\Services\Auth;

class OtpService
{
    public function generate(): string
    {
        return str_pad(
            random_int(0, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    public function expiresAt()
    {
        return now()->addMinutes(10);
    }

    public function isExpired(
        $expiresAt
    ): bool {
        return now()->greaterThan(
            $expiresAt
        );
    }
}