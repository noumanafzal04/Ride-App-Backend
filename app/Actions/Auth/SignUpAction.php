<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\DB;
use App\Repositories\Auth\UserRepository;
use App\Repositories\Auth\EmailOtpRepository;
use App\Services\Auth\OtpService;
use App\Services\Auth\EmailVerificationService;

class SignupAction
{
    public function __construct(
        private UserRepository $userRepository,
        private EmailOtpRepository $otpRepository,
        private OtpService $otpService,
        private EmailVerificationService $emailVerificationService
    ) {}

    public function execute(array $data)
    {
        $result = DB::transaction(function () use ($data) {

            $user = $this->userRepository->create([
                'first_name'   => $data['first_name'],
                'last_name'    => $data['last_name'] ?? null,
                'email'        => $data['email'],
                'phone_number' => $data['phone_number'],
                'password'     => bcrypt($data['password']),
            ]);

            $otp = $this->otpService->generate();

            $this->otpRepository->createOrUpdate(
                $user->email,
                $otp,
                now()->addMinutes(10)
            );

            return [
                'user' => $user,
                'otp'  => $otp,
            ];
        });

        try {
            $this->emailVerificationService->sendOtp(
                $result['user'],
                $result['otp']
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return $result['user'];
    }
}