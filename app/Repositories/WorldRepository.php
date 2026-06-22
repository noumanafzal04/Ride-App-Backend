<?php

namespace App\Repositories;

use App\Models\City;
use App\Repositories\BaseRepository;

class WorldRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new City();
    }

    // Closest city to a coordinate, by great-circle (haversine) distance.
    public function nearest(float $lat, float $lon): ?City
    {
        return $this->model->newQuery()
            ->select(['id', 'name', 'lat', 'lon'])
            ->selectRaw(
                '( 6371 * acos( least(1, greatest(-1,'
                . ' cos(radians(?)) * cos(radians(lat)) * cos(radians(lon) - radians(?))'
                . ' + sin(radians(?)) * sin(radians(lat)) ))) ) AS distance_km',
                [$lat, $lon, $lat]
            )
            ->orderBy('distance_km')
            ->first();
    }
}
