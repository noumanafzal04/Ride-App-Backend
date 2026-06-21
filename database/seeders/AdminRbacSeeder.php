<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminRbacSeeder extends Seeder
{
    public function run(): void
    {
        // ── Permission catalog (module → actions) ──
        $catalog = [
            'users'       => ['view', 'update', 'delete'],   // app users + profile verification
            'inspections' => ['view', 'update', 'delete'],
            'providers'   => ['view', 'update'],             // service-provider verification
            'categories'  => ['view', 'create', 'update', 'delete'],
            'reports'     => ['view'],
            'staff'       => ['view', 'create', 'update', 'delete'], // admin_users
            'roles'       => ['view', 'create', 'update', 'delete'],
            'settings'    => ['view', 'update'],
        ];

        $allKeys = [];
        foreach ($catalog as $module => $actions) {
            foreach ($actions as $action) {
                $key = "$module.$action";
                $allKeys[] = $key;
                Permission::updateOrCreate(
                    ['key' => $key],
                    ['module' => $module, 'action' => $action, 'label' => Str::title("$action $module")],
                );
            }
        }
        $idByKey = Permission::pluck('id', 'key');

        // ── Default roles ──
        $adminKeys = array_values(array_filter(
            $allKeys,
            fn($k) => !Str::startsWith($k, ['users.', 'staff.', 'roles.']),
        ));
        $employeeKeys = [
            'inspections.view', 'inspections.update',
            'providers.view', 'providers.update',
            'categories.view', 'reports.view',
        ];

        $roles = [
            ['name' => 'Super Admin', 'slug' => Role::SUPER_ADMIN, 'description' => 'Full access to everything.', 'keys' => $allKeys],
            ['name' => 'Admin',       'slug' => 'admin',           'description' => 'Manage operations; cannot manage app users or staff/roles.', 'keys' => $adminKeys],
            ['name' => 'Employee',    'slug' => 'employee',        'description' => 'Handle day-to-day module work.', 'keys' => $employeeKeys],
        ];

        foreach ($roles as $r) {
            $role = Role::updateOrCreate(
                ['slug' => $r['slug']],
                ['name' => $r['name'], 'description' => $r['description'], 'is_system' => true],
            );
            $role->permissions()->sync(collect($r['keys'])->map(fn($k) => $idByKey[$k])->all());
        }

        // ── Default Super Admin login ──
        $super = Role::where('slug', Role::SUPER_ADMIN)->first();
        AdminUser::updateOrCreate(
            ['email' => 'admin@ezride.com'],
            [
                'role_id'  => $super->id,
                'name'     => 'Super Admin',
                'password' => 'Admin@123', // hashed via model cast
                'status'   => 'active',
            ],
        );
    }
}
