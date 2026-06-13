<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\LoginAction;
use App\Actions\Auth\SignupAction;
use App\Actions\Auth\VerifyOtpAction;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;

use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\SignUpRequest;
use App\Http\Requests\Api\V1\Auth\VerifyOtpRequest;

use App\Http\Resources\Api\V1\Auth\UserResource;
use App\Http\Resources\Api\V1\Auth\LoginResource;

use App\Repositories\Auth\UserRepository;
use App\Services\Auth\EmailVerificationService;
use App\Support\ApiResponse;

class AuthController extends Controller
{
    public $resourceName = 'auth';

    public function __construct(protected SignupAction $signupAction, protected VerifyOtpAction $verifyOtpAction, protected LoginAction $loginAction) {}

    public function signup(
        SignUpRequest $request,
    ) {

        $this->signupAction->execute(
            $request->validated()
        );

        return ApiResponse::noContent(__("{$this->resourceName}.OTP_sent"));
    }

    public function verifyOtp(
        VerifyOtpRequest $request,
    ) {

        $this->verifyOtpAction->execute(
            $request->email,
            $request->otp
        );

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.'
        ]);
    }

    public function login(
        LoginRequest $request,
    ) {

        $data = $this->loginAction->execute(
            $request->email,
            $request->password
        );

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => new LoginResource($data)
        ]);
    }

    public function profile()
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource(
                auth()->user()
            )
        ]);
    }

    public function logout()
    {
        auth()
            ->user()
            ->token()
            ->revoke();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful.'
        ]);
    }

    public function resendOtp(
        UserRepository $userRepository,
        EmailVerificationService $emailVerificationService
    ) {

        $user = $userRepository
            ->findByEmail(
                request('email')
            );

        if (!$user) {
            throw new ApiException(
                'User not found.'
            );
        }

        if ($user->email_verified_at) {
            throw new ApiException(
                'Email already verified.'
            );
        }

        $emailVerificationService
            ->sendOtp($user);

        return response()->json([
            'success' => true,
            'message' => 'OTP resent successfully.'
        ]);
    }
}
