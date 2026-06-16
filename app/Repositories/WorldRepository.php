<?php

namespace App\Repositories;

use App\Models\Cities;
use App\Repositories\BaseRepository;

class WorldRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Cities();
    }
}
