<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RideBooking extends Model
{
    protected $table = 'ride_bookings';

    protected $fillable = [
        'ride_post_id',
        'passenger_id',
        'seats_booked',
        'price_per_seat',
        'total_amount',
        'note',
        'status',
    ];

    protected $casts = [
        'seats_booked'   => 'integer',
        'price_per_seat' => 'decimal:2',
        'total_amount'   => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────

    public function ridePost(): BelongsTo
    {
        return $this->belongsTo(RidePost::class, 'ride_post_id');
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }
}
