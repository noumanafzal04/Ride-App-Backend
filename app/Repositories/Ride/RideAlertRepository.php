<?php

namespace App\Repositories\Ride;

use App\Models\RideAlert;
use App\Repositories\BaseRepository;

class RideAlertRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new RideAlert();
    }

    /**
     * Active alerts that match a newly posted ride's route (and date, if the
     * alert specified one). Excludes the driver themself. Index-backed.
     */
    public function matchingForPost(int $fromCityId, int $toCityId, ?string $date, int $excludeUserId)
    {
        return $this->model->newQuery()
            ->where('is_active', true)
            ->where('from_city_id', $fromCityId)
            ->where('to_city_id', $toCityId)
            ->where('user_id', '!=', $excludeUserId)
            ->where(function ($q) use ($date) {
                $q->whereNull('alert_date');
                if ($date) {
                    $q->orWhereDate('alert_date', $date);
                }
            })
            ->get();
    }
}
