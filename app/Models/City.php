<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $table = 'cities';

    protected $fillable = ['name', 'province', 'lat', 'lon'];

//    protected $casts = [
//        'status' => 'boolean',
//    ];

    public function distancesFrom() {
        return $this->hasMany(CityDistance::class, 'from_city_id');
    }

    public function ridePostsFrom(): HasMany
    {
        return $this->hasMany(RidePost::class, 'from_city_id');
    }

    public function ridePostsTo(): HasMany
    {
        return $this->hasMany(RidePost::class, 'to_city_id');
    }
}
