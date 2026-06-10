<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverProfile extends Model
{
    protected $fillable = [
        'user_id',
        'cnic_number',
        'cnic_front_image',
        'cnic_back_image',
        'driving_license_no',
        'license_front_image',
        'license_back_image',
        'license_expiry_date',
        'rating',
        'total_trips',
        'total_earnings',
        'verification_status',
        'status',
        'online_status',
    ];

    protected $casts = [
        'license_expiry_date' => 'date',
        'online_status'       => 'boolean',
        'rating'              => 'decimal:2',
        'status'              => Status::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
