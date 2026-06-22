<?php

namespace App\Repositories\Notification;

use App\Models\DeviceToken;
use App\Repositories\BaseRepository;

class DeviceTokenRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new DeviceToken();
    }

    // A token is unique per device — re-point it to the current user.
    public function register(int $userId, string $token, ?string $platform): void
    {
        $this->model->newQuery()->updateOrCreate(
            ['token' => $token],
            ['user_id' => $userId, 'platform' => $platform],
        );
    }

    public function remove(int $userId, string $token): void
    {
        $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('token', $token)
            ->delete();
    }
}
