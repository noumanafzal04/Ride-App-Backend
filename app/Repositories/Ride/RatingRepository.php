<?php

namespace App\Repositories\Ride;

use App\Models\Rating;
use App\Repositories\BaseRepository;

class RatingRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Rating();
    }

    // Average rating a user has received (optionally per module type)
    public function avgForUser(int $userId, ?string $type = null): float
    {
        $query = $this->model->where('to_user_id', $userId);

        if ($type) {
            $query->where('type', $type);
        }

        return round((float) $query->avg('rating'), 2);
    }
}
