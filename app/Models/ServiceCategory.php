<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    protected $table = 'service_categories';

    protected $fillable = ['name', 'slug', 'icon', 'sort', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort'      => 'integer',
    ];
}
