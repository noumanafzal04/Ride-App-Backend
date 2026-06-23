<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalBooking extends Model
{
    public const STATUS_REQUESTED = 'requested';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REJECTED  = 'rejected';

    protected $fillable = [
        'rental_car_id', 'customer_id', 'owner_id', 'start_date', 'end_date', 'days',
        'with_driver', 'pickup_location', 'total_amount', 'deposit', 'status', 'notes',
    ];

    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'with_driver'  => 'boolean',
        'total_amount' => 'decimal:2',
        'deposit'      => 'decimal:2',
    ];

    public function rentalCar(): BelongsTo { return $this->belongsTo(RentalCar::class); }
    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'customer_id'); }
    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'owner_id'); }
}
