<?php

namespace App\Services\Auth;

use App\Models\User;

class TokenService
{
    public function createToken(
        User $user
    ): string {

        return $user
            ->createToken(
                'auth_token'
            )
            ->accessToken;
    }

    public function revokeCurrentToken(
        User $user
    ): void {

        $user
            ->token()
            ?->revoke();
    }
}