<?php

namespace App\Actions\Notification;

use App\Repositories\Notification\DeviceTokenRepository;

class DeviceTokenAction
{
    public function __construct(protected DeviceTokenRepository $repository) {}

    public function register(int $userId, string $token, ?string $platform): void
    {
        $this->repository->register($userId, $token, $platform);
    }

    public function remove(int $userId, string $token): void
    {
        $this->repository->remove($userId, $token);
    }
}
