<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // ── Reference data ──
            PakPunjabCitiesSeeder::class,   // cities
            VehicleMakeModelSeeder::class,  // vehicle makes + models

            // ── App config ──
            AppModuleSeeder::class,         // which modules are live (Rides + Inspection on)
            AdminRbacSeeder::class,         // permissions, roles, super-admin login

            // ── Billing (order matters) ──
            BillingSeeder::class,           // per-module free limits + sample plans (billing_plans)
            RidePassSeeder::class,          // overrides RIDE → 2 free posts + 24h pass (must run AFTER BillingSeeder)
            FeatureSettingSeeder::class,    // paid "feature a listing" price + duration per module
        ]);

        // NOTE — intentionally NOT run automatically:
        //   • SubscriptionPlanSeeder — legacy `subscription_plans` table (dead; replaced by BillingSeeder/RidePassSeeder → billing_plans).
        //   • DemoSeeder — sample listings/rides; needs app users first. Run manually after signups:
        //       php artisan db:seed --class=DemoSeeder
    }
}
