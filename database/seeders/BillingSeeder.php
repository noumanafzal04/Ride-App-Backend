<?php

namespace Database\Seeders;

use App\Constants\BillingModule;
use App\Models\ModuleBillingSetting;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        // Per-module free tier + enforcement (OFF at launch → everyone posts free).
        $settings = [
            BillingModule::BUYSELL => 2, // 2 concurrent active listings free
            BillingModule::SERVICE => 2, // 2 categories free
            BillingModule::RIDE    => 1, // 1 free post (welcome)
            BillingModule::RENTAL  => 1, // 1 free listing (welcome)
        ];
        foreach ($settings as $module => $freeLimit) {
            ModuleBillingSetting::updateOrCreate(
                ['module' => $module],
                [
                    'free_mode'           => BillingModule::FREE_MODE[$module],
                    'free_limit'          => $freeLimit,
                    'enforcement_enabled' => false,
                ],
            );
        }

        // Sample plans (price 0 placeholders — set real prices in the portal).
        $tiers = [
            ['name' => 'Basic',   'duration_days' => 1,  'post_limit' => 5,  'sort' => 1],
            ['name' => 'Weekly',  'duration_days' => 7,  'post_limit' => 20, 'sort' => 2],
            ['name' => 'Monthly', 'duration_days' => 30, 'post_limit' => 30, 'sort' => 3],
        ];
        foreach (BillingModule::ALL as $module) {
            foreach ($tiers as $t) {
                SubscriptionPlan::updateOrCreate(
                    ['module' => $module, 'name' => $t['name']],
                    [
                        'duration_days' => $t['duration_days'],
                        'post_limit'    => $t['post_limit'],
                        'price'         => 0,
                        'is_active'     => true,
                        'sort'          => $t['sort'],
                    ],
                );
            }
        }
    }
}
