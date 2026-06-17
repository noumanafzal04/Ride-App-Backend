<?php

namespace App\Repositories\User;

use App\Constants\ResourceFields;
use App\Models\UserProfile;
use App\Repositories\BaseRepository;
use App\Traits\PreparesDBPayload;

class UserProfileRepository extends BaseRepository
{
    use PreparesDBPayload;
    public function __construct()
    {
        $this->model = new UserProfile();
    }

    // UserProfileRepository.php
    public function updateOrCreateForUser(int $userId, array $data): UserProfile
    {
        $payload = $this->preparePayload($data, ResourceFields::USER_PROFILE_CREATE_FIELDS);

        return $this->model->updateOrCreate(
            ['user_id' => $userId],
            $payload
        );
    }
}
