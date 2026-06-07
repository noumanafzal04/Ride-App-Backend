<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Exceptions\ApiException;
use App\Repositories\Auth\UserRepository;
use App\Repositories\Auth\EmailOtpRepository;
use App\Services\Auth\OtpService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VerifyOtpAction
{
    public function __construct(
        private UserRepository $userRepository,
        private EmailOtpRepository $otpRepository,
        private OtpService $otpService
    ) {}

    /**
     * Verify Email OTP
     *
     * @throws ApiException
     */
    public function execute(
        string $email,
        string $otp
    ): bool {

        $user = $this->userRepository
            ->findByEmail($email);

        if (!$user) {
            throw new ApiException(
                'User not found.',
                404
            );
        }

        if ($user->email_verified_at) {
            throw new ApiException(
                'Email already verified.',
                422
            );
        }

        $otpRecord = $this->otpRepository
            ->findByEmail($email);

        if (!$otpRecord) {
            throw new ApiException(
                'OTP not found.',
                404
            );
        }

        if ($otpRecord->verified_at) {
            throw new ApiException(
                'OTP already used.',
                422
            );
        }

        if (
            $this->otpService->isExpired(
                $otpRecord->expires_at
            )
        ) {
            throw new ApiException(
                'OTP has expired.',
                422
            );
        }

        if ($otpRecord->attempts >= 5) {
            throw new ApiException(
                'Maximum OTP attempts exceeded.'
            );
        }

        if (
            !Hash::check(
                $otp,
                $otpRecord->otp
            )
        ) {

            $this->otpRepository
                ->incrementAttempts(
                    $otpRecord
                );

            throw new ApiException(
                'Invalid OTP.',
                422
            );
        }

        DB::transaction(function () use (
            $user,
            $otpRecord
        ) {

            $this->userRepository
                ->verifyEmail($user);

            $this->otpRepository
                ->markVerified(
                    $otpRecord
                );

            $this->otpRepository
                ->deleteByEmail(
                    $user->email
                );
        });

        return true;
    }
}