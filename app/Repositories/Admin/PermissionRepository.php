<?php

namespace App\Repositories\Admin;

use App\Models\Permission;
use App\Repositories\BaseRepository;

class PermissionRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Permission();
    }

    public function allOrdered()
    {
        return $this->model->newQuery()->orderBy('module')->orderBy('id')->get();
    }

    public function idsForKeys(array $keys): array
    {
        return $this->model->newQuery()->whereIn('key', $keys)->pluck('id')->all();
    }
}
