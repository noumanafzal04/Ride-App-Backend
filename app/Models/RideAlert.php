<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RideAlert extends Model
{
    protected $table = 'ride_alerts';

    protected $fillable = [
        'user_id',
        'from_city_id',
        'to_city_id',
        'alert_date',
        'is_active',
        'last_notified_at',
    ];

    protected $casts = [
        'alert_date'       => 'date',
        'is_active'        => 'boolean',
        'last_notified_at' => 'datetime',
    ];

    public function fromCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'from_city_id');
    }

    public function toCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'to_city_id');
    }
}
