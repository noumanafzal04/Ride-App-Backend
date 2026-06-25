<?php

namespace Database\Seeders;

use App\Constants\BillingModule;
use App\Models\ModuleBillingSetting;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

// 24-hour ride pass model:
//   • 2 free ride posts (lifetime), then
//   • a single 24h pass that allows unlimited posting for 24 hours.
// Replaces any older multi-tier ride plans.
class RidePassSeeder extends Seeder
{
    public function run(): void
    {
        // Enforce the free cap for rides.
        ModuleBillingSetting::updateOrCreate(
            ['module' => BillingModule::RIDE],
            ['free_mode' => 'intro_credit', 'free_limit' => 2, 'enforcement_enabled' => true],
        );

        // Retire previous ride plans, keep exactly one 24h pass.
        SubscriptionPlan::where('module', BillingModule::RIDE)->delete();

        SubscriptionPlan::create([
            'module'        => BillingModule::RIDE,
            'name'          => '24-Hour Ride Pass',
            'duration_days' => 1,          // 24 hours
            'post_limit'    => 1000,       // effectively unlimited within the 24h window
            'price'         => 150,        // PKR — adjust from admin Billing
            'is_active'     => true,
            'sort'          => 1,
        ]);
    }
}
