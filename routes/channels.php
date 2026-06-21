<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private per-user channel — booking status changes, notifications, alerts, chat badge.
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private conversation channel — only the two participants may subscribe.
Broadcast::channel('conversation.{id}', function ($user, $id) {
    $conversation = \App\Models\Conversation::find($id);
    return $conversation && $conversation->isParticipant((int) $user->id);
});

// Shared admin channel — any authenticated admin_user (panel) may subscribe.
Broadcast::channel('admin', function ($user) {
    return $user instanceof \App\Models\AdminUser;
}, ['guards' => ['sanctum']]);

// Public per-route channel — anyone browsing this route gets new posts live.
// (No auth: only non-sensitive ride summaries are broadcast here.)

