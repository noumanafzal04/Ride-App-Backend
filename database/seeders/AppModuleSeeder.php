<?php

namespace Database\Seeders;

use App\Models\AppModule;
use Illuminate\Database\Seeder;

class AppModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Defaults: only Rides + Inspection are live. Admin can flip the rest on
        // later from the Module Settings page. updateOrCreate so re-running never
        // clobbers an admin's chosen enabled state (only fills missing rows).
        $modules = [
            ['key' => 'ride',        'name' => 'Rides',        'icon' => 'car-multiple',    'enabled' => true,  'sort' => 1],
            ['key' => 'inspection',  'name' => 'Inspection',   'icon' => 'clipboard-check', 'enabled' => true,  'sort' => 2],
            ['key' => 'rental',      'name' => 'Rent a Car',   'icon' => 'car-key',         'enabled' => false, 'sort' => 3],
            ['key' => 'service',     'name' => 'Car Services', 'icon' => 'wrench',          'enabled' => false, 'sort' => 4],
            ['key' => 'marketplace', 'name' => 'Buy / Sell',   'icon' => 'tag',             'enabled' => false, 'sort' => 5],
        ];

        foreach ($modules as $m) {
            AppModule::firstOrCreate(['key' => $m['key']], $m);
        }
    }
}
