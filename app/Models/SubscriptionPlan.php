<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $table = 'billing_plans';

    protected $fillable = ['module', 'name', 'duration_days', 'post_limit', 'price', 'is_active', 'sort'];

    protected $casts = [
        'duration_days' => 'integer',
        'post_limit'    => 'integer',
        'price'         => 'decimal:2',
        'is_active'     => 'boolean',
        'sort'          => 'integer',
    ];
}
