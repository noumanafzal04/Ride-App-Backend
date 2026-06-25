<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppModule extends Model
{
    protected $fillable = ['key', 'name', 'icon', 'enabled', 'sort'];

    protected $casts = ['enabled' => 'boolean'];
}
