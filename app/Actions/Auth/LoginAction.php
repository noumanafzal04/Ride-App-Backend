<?php

namespace App\Actions\Auth;

use App\Exceptions\ApiException;
use App\Repositories\Auth\UserRepository;
use App\Services\Auth\TokenService;
use Illuminate\Support\Facades\Hash;

class LoginAction
{
    public function __construct(
        private UserRepository $userRepository,
        private TokenService $tokenService
    ) {}

    public function execute(
        string $email,
        string $password
    ): array {

        $user = $this->userRepository
            ->findByEmail($email);

        if (!$user) {
            throw new ApiException(
                'Invalid credentials.'
            );
        }

        if (
            !Hash::check(
                $password,
                $user->password
            )
        ) {
            throw new ApiException(
                'Invalid credentials.'
            );
        }

        if (!$user->email_verified_at) {
            throw new ApiException(
                'Please verify your email first.'
            );
        }

        $token = $this->tokenService
            ->createToken($user);

        return [
            'user' => $user,
            'token' => $token
        ];
    }
}