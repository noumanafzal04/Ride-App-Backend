<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverProfile extends Model
{
    protected $fillable = [
        'user_id',
        'cnic_number',
        'cnic_front_image',
        'cnic_back_image',
        'license_number',       // was driving_license_no
        'license_front_image',
        'license_back_image',
        'rating_avg',           // was rating
        'total_trips',
        'total_earnings',
        'verification_status',
        'is_online',            // was online_status
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'rating_avg'          => 'decimal:2',
        'is_online'           => 'boolean',
        'verified_at'         => 'datetime',
        'total_trips'         => 'integer',
        'total_earnings'      => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
