<?php

namespace App\Services\Billing;

use App\Constants\BillingModule;
use App\Exceptions\ApiException;
use App\Models\CarListing;
use App\Models\ModuleBillingSetting;
use App\Models\RidePost;
use App\Models\ServiceProvider;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Carbon;

class BillingService
{
    public function settings(string $module): ModuleBillingSetting
    {
        return ModuleBillingSetting::firstOrCreate(
            ['module' => $module],
            ['free_mode' => BillingModule::FREE_MODE[$module] ?? 'intro_credit', 'free_limit' => 0, 'enforcement_enabled' => false],
        );
    }

    public function activeSubscription(int $userId, string $module): ?Subscription
    {
        return Subscription::where('user_id', $userId)
            ->where('module', $module)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where('ends_at', '>', now())
            ->whereColumn('posts_used', '<', 'posts_allowed')
            ->latest('ends_at')
            ->first();
    }

    // How much of the free allowance the user has consumed for this module.
    public function usageCount(int $userId, string $module): int
    {
        return match ($module) {
            BillingModule::RIDE    => (int) RidePost::where('driver_id', $userId)->count(),               // lifetime posts
            BillingModule::BUYSELL => (int) CarListing::where('user_id', $userId)
                ->whereIn('status', [CarListing::STATUS_ACTIVE, CarListing::STATUS_PENDING])->count(),     // concurrent active/pending
            BillingModule::SERVICE => (int) optional(ServiceProvider::where('user_id', $userId)->first())
                ->categories()->count(),                                                                   // current categories
            BillingModule::RENTAL  => (int) \App\Models\RentalCar::where('user_id', $userId)
                ->whereIn('status', [\App\Models\RentalCar::STATUS_ACTIVE, \App\Models\RentalCar::STATUS_PENDING])->count(),
            default                => 0,
        };
    }

    /**
     * Decide whether the user may create one more item in this module.
     * Returns a structured result; never throws.
     */
    public function check(int $userId, string $module): array
    {
        $cfg = $this->settings($module);

        // Launch-free: enforcement off → everyone posts free.
        if (!$cfg->enforcement_enabled) {
            return ['allowed' => true, 'free' => true, 'enforcement' => false];
        }

        $used = $this->usageCount($userId, $module);
        if ($used < $cfg->free_limit) {
            return ['allowed' => true, 'free' => true, 'enforcement' => true,
                'free_limit' => $cfg->free_limit, 'free_used' => $used, 'free_left' => $cfg->free_limit - $used];
        }

        $sub = $this->activeSubscription($userId, $module);
        if ($sub) {
            return ['allowed' => true, 'free' => false, 'enforcement' => true, 'subscription' => $sub];
        }

        return ['allowed' => false, 'free' => false, 'enforcement' => true, 'needs_plan' => true];
    }

    // Gate used by module actions before creating. Throws 402 if not allowed.
    public function assertCanPost(int $userId, string $module): array
    {
        $r = $this->check($userId, $module);
        if (!$r['allowed']) {
            throw new ApiException('You need an active plan to post in this section.', 402);
        }
        return $r;
    }

    // Record one consumed post against the active subscription (only when not free).
    public function consume(int $userId, string $module, ?array $checkResult = null): void
    {
        $r = $checkResult ?? $this->check($userId, $module);
        if (($r['free'] ?? true) === true) {
            return; // free post — nothing to consume
        }
        $sub = $r['subscription'] ?? $this->activeSubscription($userId, $module);
        if (!$sub) {
            return;
        }
        $sub->increment('posts_used');
        if ($sub->fresh()->posts_used >= $sub->posts_allowed) {
            $sub->update(['status' => Subscription::STATUS_EXPIRED]);
        }
    }

    // Activate a plan for a user (self-subscribe or admin grant).
    public function activatePlan(int $userId, SubscriptionPlan $plan, string $source = 'self', ?float $pricePaid = null): Subscription
    {
        return Subscription::create([
            'user_id'       => $userId,
            'module'        => $plan->module,
            'plan_id'       => $plan->id,
            'posts_allowed' => $plan->post_limit,
            'posts_used'    => 0,
            'starts_at'     => now(),
            'ends_at'       => Carbon::now()->addDays($plan->duration_days),
            'status'        => Subscription::STATUS_ACTIVE,
            'source'        => $source,
            'price_paid'    => $pricePaid ?? $plan->price,
        ]);
    }

    // Snapshot of the user's status across all modules (for the mobile membership screen).
    public function statusFor(int $userId): array
    {
        $out = [];
        foreach (BillingModule::ALL as $module) {
            $cfg = $this->settings($module);
            $sub = $this->activeSubscription($userId, $module);
            $used = $cfg->enforcement_enabled ? $this->usageCount($userId, $module) : 0;
            $out[] = [
                'module'              => $module,
                'enforcement_enabled' => $cfg->enforcement_enabled,
                'free_mode'           => $cfg->free_mode,
                'free_limit'          => $cfg->free_limit,
                'free_used'           => $used,
                'free_left'           => max(0, $cfg->free_limit - $used),
                'has_active_plan'     => (bool) $sub,
                'posts_left'          => $sub?->postsLeft() ?? 0,
                'plan_ends_at'        => $sub?->ends_at?->toISOString(),
            ];
        }
        return $out;
    }
}
