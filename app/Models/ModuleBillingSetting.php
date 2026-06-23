<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleBillingSetting extends Model
{
    protected $fillable = ['module', 'free_mode', 'free_limit', 'enforcement_enabled'];

    protected $casts = [
        'free_limit'          => 'integer',
        'enforcement_enabled' => 'boolean',
    ];
}
