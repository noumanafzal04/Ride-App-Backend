<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private per-user channel — booking status changes, notifications, alerts.
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Public per-route channel — anyone browsing this route gets new posts live.
// (No auth: only non-sensitive ride summaries are broadcast here.)

