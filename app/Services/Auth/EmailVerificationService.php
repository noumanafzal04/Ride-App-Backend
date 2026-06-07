<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Events\UserRegistered;
use App\Repositories\Auth\EmailOtpRepository;

class EmailVerificationService
{
    public function __construct(
        private OtpService $otpService,
        private EmailOtpRepository $otpRepository
    ) {}

    public function sendOtp(
        User $user
    ): void {

        $otp = $this->otpService->generate();

        $this->otpRepository
            ->createOrUpdate(
                $user->email,
                $otp,
                $this->otpService->expiresAt()
            );

        event(
            new UserRegistered(
                $user,
                $otp
            )
        );
    }
}