<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Plans: Free, 1 Day, 7 Days, 30 Days.
     * Everything is free for now (price = 0). Data/history is still saved.
     * post_limit: number of ride posts allowed under the plan.
     *   -1  = unlimited
     *    0  = no posting allowed
     *   set these manually now; can be changed later (or driven dynamically).
     */
    public function run(): void
    {
        $now = now();

        $plans = [
            [
                'name'          => 'Free',
                'slug'          => 'free',
                'duration_days' => 0,     // no expiry
                'price'         => 0,
                'post_limit'    => 2,     // e.g. 2 posts on free (adjust manually)
                'description'   => 'Free plan with limited posts.',
                'sort_order'    => 1,
            ],
            [
                'name'          => '1 Day',
                'slug'          => 'day_1',
                'duration_days' => 1,
                'price'         => 0,     // free for now, set price later
                'post_limit'    => 5,
                'description'   => '1-day plan.',
                'sort_order'    => 2,
            ],
            [
                'name'          => '7 Days',
                'slug'          => 'day_7',
                'duration_days' => 7,
                'price'         => 0,
                'post_limit'    => 20,
                'description'   => '7-day plan.',
                'sort_order'    => 3,
            ],
            [
                'name'          => '30 Days',
                'slug'          => 'day_30',
                'duration_days' => 30,
                'price'         => 0,
                'post_limit'    => 100,
                'description'   => '30-day plan.',
                'sort_order'    => 4,
            ],
        ];

        foreach ($plans as $plan) {
            DB::table('subscription_plans')->insert(array_merge($plan, [
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
