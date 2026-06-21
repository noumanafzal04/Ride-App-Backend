<?php

namespace App\Actions\Admin;

use App\Exceptions\ApiException;
use App\Models\Role;
use App\Repositories\Admin\PermissionRepository;
use App\Repositories\Admin\RoleRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleAction
{
    public function __construct(
        protected RoleRepository $roles,
        protected PermissionRepository $permissions,
    ) {}

    public function list()
    {
        return $this->roles->allWithMeta();
    }

    public function catalog()
    {
        return $this->permissions->allOrdered();
    }

    public function show(int $id): Role
    {
        return $this->roles->findWithPermissions($id);
    }

    public function create(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $role = $this->roles->create([
                'name'        => $data['name'],
                'slug'        => $this->uniqueSlug($data['name']),
                'description' => $data['description'] ?? null,
                'is_system'   => false,
            ]);
            $this->roles->syncPermissions($role, $this->permissions->idsForKeys($data['permissions'] ?? []));
            return $this->roles->findWithPermissions($role->id);
        });
    }

    public function update(int $id, array $data): Role
    {
        return DB::transaction(function () use ($id, $data) {
            $role = $this->roles->findOrFail($id);

            // Name/description editable for all; slug is preserved so the
            // superadmin bypass never breaks.
            $update = [];
            if (array_key_exists('name', $data)) $update['name'] = $data['name'];
            if (array_key_exists('description', $data)) $update['description'] = $data['description'];
            if (!empty($update)) $this->roles->update($id, $update);

            if (array_key_exists('permissions', $data)) {
                $this->roles->syncPermissions($role, $this->permissions->idsForKeys($data['permissions']));
            }

            return $this->roles->findWithPermissions($id);
        });
    }

    public function destroy(int $id): void
    {
        $role = $this->roles->findOrFail($id);

        if ($role->is_system) {
            throw new ApiException('Default roles cannot be deleted.', 422);
        }
        if ($role->adminUsers()->count() > 0) {
            throw new ApiException('This role is assigned to staff. Reassign them first.', 422);
        }

        $this->roles->deleteById($id);
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'role';
        $slug = $base;
        $i = 2;
        while ($this->roles->findOne(callback: fn($q) => $q->where('slug', $slug))) {
            $slug = "$base-$i";
            $i++;
        }
        return $slug;
    }
}
