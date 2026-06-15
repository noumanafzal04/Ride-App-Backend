<?php

namespace App\Repositories\Driver;

use App\Models\DriverProfile;
use App\Repositories\BaseRepository;

class DriverProfileRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new DriverProfile();
    }

    // DriverProfileRepository.php

    public function updateOrCreateForUser(int $userId, array $data): DriverProfile
    {
        return $this->model->updateOrCreate(['user_id' => $userId], $data);
    }

    public function existsForUser(int $userId): bool
    {
        return $this->model->where('user_id', $userId)->exists();
    }
}
