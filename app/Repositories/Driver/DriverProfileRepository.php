<?php

namespace App\Repositories\Driver;

use App\Constants\ResourceFields;
use App\Models\DriverProfile;
use App\Repositories\BaseRepository;
use App\Traits\PreparesDBPayload;

class DriverProfileRepository extends BaseRepository
{
    use PreparesDBPayload;
    public function __construct()
    {
        $this->model = new DriverProfile();
    }

    // DriverProfileRepository.php

    public function updateOrCreateForUser(int $userId, array $data): DriverProfile
    {
        $payload = $this->preparePayload($data, ResourceFields::DRIVER_PROFILE_CREATE_FIELDS);

        return $this->model->updateOrCreate(
            ['user_id' => $userId],
            $payload
        );
    }
    public function existsForUser(int $userId): bool
    {
        return $this->model->where('user_id', $userId)->exists();
    }

    public function incrementTripsForUser(int $userId): void
    {
        $this->model->where('user_id', $userId)->increment('total_trips');
    }

    public function setRatingAvgForUser(int $userId, float $avg): void
    {
        $this->model->where('user_id', $userId)->update(['rating_avg' => $avg]);
    }
}
