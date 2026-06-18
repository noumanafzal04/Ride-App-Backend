<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CityDistance extends Model {
    protected $fillable = [
        'from_city_id', 'to_city_id',
        'distance_km', 'duration_min'
    ];

    public function fromCity() {
        return $this->belongsTo(City::class, 'from_city_id');
    }

    public function toCity() {
        return $this->belongsTo(City::class, 'to_city_id');
    }
}
