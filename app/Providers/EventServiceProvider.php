<?php

namespace App\Providers;

use App\Events\UserRegistered;
use App\Listeners\SendRegistrationOtp;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [

        UserRegistered::class => [
            SendRegistrationOtp::class,
        ],

    ];

    public function boot(): void
    {
        //
    }
}