<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $table = 'cities';

    protected $fillable = ['name', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function ridePostsFrom(): HasMany
    {
        return $this->hasMany(RidePost::class, 'from_city_id');
    }

    public function ridePostsTo(): HasMany
    {
        return $this->hasMany(RidePost::class, 'to_city_id');
    }
}
