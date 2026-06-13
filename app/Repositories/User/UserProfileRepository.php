<?php

namespace App\Repositories\User;

use App\Models\UserProfile;
use App\Repositories\BaseRepository;

class UserProfileRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new UserProfile();
    }

    public function updateOrCreateForUser(int $userId, array $data): UserProfile
    {
        return $this->model->updateOrCreate(
            ['user_id' => $userId],
            $data
        );
    }

    public function findByUserId(int $userId): ?UserProfile
    {
        return $this->model->where('user_id', $userId)->first();
    }
}
