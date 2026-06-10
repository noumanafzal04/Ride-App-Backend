<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLocation extends Model
{
    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'address',
        'located_at',
    ];

    protected $casts = [
        'latitude'   => 'decimal:7',
        'longitude'  => 'decimal:7',
        'located_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
