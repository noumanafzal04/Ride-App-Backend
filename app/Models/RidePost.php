<?php
// app/Models/RidePost.php

namespace App\Models;

use App\Constants\ResourceFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RidePost extends Model
{
    protected $table = 'ride_posts';

    protected $fillable = [
        'driver_id',
        'from_city_id',
        'to_city_id',
        'from_address',
        'to_address',
        'from_latitude',
        'from_longitude',
        'to_latitude',
        'to_longitude',
        'departure_at',
        'available_seats',
        'price_per_seat',
        'luggage_allowed',
        'notes',
        'post_type',
        'status',
    ];

    protected $casts = [
        'from_latitude'   => 'decimal:7',
        'from_longitude'  => 'decimal:7',
        'to_latitude'     => 'decimal:7',
        'to_longitude'    => 'decimal:7',
        'departure_at'    => 'datetime',
        'available_seats' => 'integer',
        'price_per_seat'  => 'decimal:2',
        'luggage_allowed' => 'boolean',
    ];

    public const RESOURCE_RELATIONS = [
        'driver' => [
            'select' => ResourceFields::RIDE_POST_DRIVER_FIELDS,
            'show'   => ResourceFields::RIDE_POST_DRIVER_FIELDS,
        ],
        'driver.vehicles' => [
            'select' => ResourceFields::RIDE_POST_VEHICLE_FIELDS,
            'show'   => ResourceFields::RIDE_POST_VEHICLE_FIELDS,
        ],
        'driver.vehicles.vehicleModel' => [
            'select' => ResourceFields::VEHICLE_MODEL_FIELDS,
            'show'   => ResourceFields::VEHICLE_MODEL_FIELDS,
        ],
        'driver.vehicles.vehicleModel.make' => [
            'select' => ResourceFields::VEHICLE_MAKE_FIELDS,
            'show'   => ResourceFields::VEHICLE_MAKE_FIELDS,
        ],
        'fromCity' => [
            'select' => ResourceFields::CITY_FIELDS,
            'show'   => ResourceFields::CITY_FIELDS,
        ],
        'toCity' => [
            'select' => ResourceFields::CITY_FIELDS,
            'show'   => ResourceFields::CITY_FIELDS,
        ],
    ];

    // ─── Relationships ────────────────────────────────────

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function fromCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'from_city_id');
    }

    public function toCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'to_city_id');
    }
}
