<?php

namespace Database\Seeders;

use App\Models\FeatureSetting;
use App\Services\Feature\FeatureService;
use Illuminate\Database\Seeder;

class FeatureSettingSeeder extends Seeder
{
    public function run(): void
    {
        // Default price + duration for the paid "feature" boost, per module.
        // Editable later from the admin panel.
        $defaults = [
            FeatureService::MODULE_BUYSELL => ['price' => 500, 'duration_days' => 7],
            FeatureService::MODULE_RENTAL  => ['price' => 500, 'duration_days' => 7],
        ];

        foreach ($defaults as $module => $cfg) {
            FeatureSetting::firstOrCreate(
                ['module' => $module],
                ['price' => $cfg['price'], 'duration_days' => $cfg['duration_days'], 'is_active' => true],
            );
        }
    }
}
