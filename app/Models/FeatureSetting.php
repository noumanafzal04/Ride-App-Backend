<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureSetting extends Model
{
    protected $fillable = ['module', 'price', 'duration_days', 'is_active'];

    protected $casts = [
        'price'         => 'decimal:2',
        'duration_days' => 'integer',
        'is_active'     => 'boolean',
    ];
}
