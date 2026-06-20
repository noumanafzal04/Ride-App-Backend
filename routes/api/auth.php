<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;

Route::prefix('auth')
    ->group(function () {

        Route::post(
            '/signup',
            [AuthController::class, 'signup']
        );

        Route::post(
            '/verify-otp',
            [AuthController::class, 'verifyOtp']
        );

        Route::post(
            '/resend-otp',
            [AuthController::class, 'resendOtp']
        );

        Route::post(
            '/login',
            [AuthController::class, 'login']
        );

        Route::middleware('auth:api')
            ->group(function () {

                Route::get(
                    '/me',
                    [AuthController::class, 'profile']
                );

                Route::post(
                    '/profile',
                    [\App\Http\Controllers\Api\V1\Profile\ProfileController::class, 'update']
                );

                Route::post(
                    '/logout',
                    [AuthController::class, 'logout']
                );
            });
    });
