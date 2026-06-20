<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionCategory extends Model
{
    protected $table = 'inspection_categories';

    protected $fillable = ['name', 'slug', 'sort'];

    public $timestamps = true;
}
