<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cities extends Model
{
    protected $fillable = [
        'id',
        'name',
        'status',

    ];

    protected function casts(): array
    {
        return [];
    }
}
