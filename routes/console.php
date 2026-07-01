<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Safety net: close rides left open past departure + 2h grace so a driver who
// forgets to end a ride is never stuck (one-active-post rule).
Schedule::command('rides:close-stale')->everyTenMinutes();

// Keep the chat tables lean: drop conversations closed > 30 days ago (messages cascade).
Schedule::command('chat:purge-closed')->daily();

// Drop the "featured" flag once a paid feature window has ended.
Schedule::command('features:expire')->hourly();
