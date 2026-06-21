<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $table = 'conversations';

    public const STATUS_OPEN   = 'open';
    public const STATUS_CLOSED = 'closed';

    public const TYPE_RIDE    = 'ride';
    public const TYPE_SERVICE = 'service';

    protected $fillable = [
        'type',
        'booking_id',
        'ride_post_id',
        'service_booking_id',
        'driver_id',
        'rider_id',
        'status',
        'last_message_preview',
        'last_message_at',
        'driver_unread',
        'rider_unread',
        'closed_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'closed_at'       => 'datetime',
        'driver_unread'   => 'integer',
        'rider_unread'    => 'integer',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function ridePost(): BelongsTo
    {
        return $this->belongsTo(RidePost::class, 'ride_post_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(RideBooking::class, 'booking_id');
    }

    public function serviceBooking(): BelongsTo
    {
        return $this->belongsTo(ServiceBooking::class, 'service_booking_id');
    }

    public function isParticipant(int $userId): bool
    {
        return $this->driver_id === $userId || $this->rider_id === $userId;
    }

    /** The "other" user's id from a given viewer's perspective. */
    public function otherUserId(int $viewerId): int
    {
        return $viewerId === $this->driver_id ? $this->rider_id : $this->driver_id;
    }
}
