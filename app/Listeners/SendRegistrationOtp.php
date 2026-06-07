<?php

namespace App\Listeners;

use App\Mail\SendOtpMail;
use App\Events\UserRegistered;
use Illuminate\Support\Facades\Mail;

class SendRegistrationOtp
{
    public function handle(
        UserRegistered $event
    ): void {

        Mail::to(
            $event->user->email
        )->send(
            new SendOtpMail(
                $event->user,
                $event->otp
            )
        );
    }
}