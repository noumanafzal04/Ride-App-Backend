<?php

namespace App\Repositories\Admin;

use App\Models\Role;
use App\Repositories\BaseRepository;

class RoleRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Role();
    }

    public function allWithMeta()
    {
        return $this->model->newQuery()
            ->withCount(['permissions', 'adminUsers'])
            ->orderBy('id')
            ->get();
    }

    public function findWithPermissions(int $id): Role
    {
        return $this->model->newQuery()->with('permissions:id,key')->findOrFail($id);
    }

    public function syncPermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
    }
}
