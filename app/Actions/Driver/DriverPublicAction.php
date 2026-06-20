<?php

namespace App\Actions\Driver;

use App\Actions\BaseAction\BaseAction;
use App\Repositories\Driver\DriverProfileRepository;
use App\Repositories\Driver\RidePostRepository;
use App\Repositories\Ride\RatingRepository;

class DriverPublicAction extends BaseAction
{
    public function __construct(
        RidePostRepository $repository,
        protected RatingRepository $ratings,
        protected DriverProfileRepository $driverProfiles,
    ) {
        parent::__construct($repository, 'driver');
    }

    // Lightweight aggregates for the stats row (single numbers).
    public function summary(int $driverId): array
    {
        $profile = $this->driverProfiles->forUser($driverId);

        return [
            'rating_avg'    => $profile ? (float) $profile->rating_avg : 0,
            'total_trips'   => $profile ? (int) $profile->total_trips : 0,
            'reviews_count' => $this->ratings->countForDriver($driverId),
        ];
    }

    // Paginated reviews received by the driver.
    public function reviews(int $driverId)
    {
        return $this->ratings->paginatedReceivedForDriver($driverId);
    }

    // Paginated completed trips for the driver.
    public function trips(int $driverId)
    {
        return $this->repository->paginatedCompletedForDriver($driverId);
    }
}
