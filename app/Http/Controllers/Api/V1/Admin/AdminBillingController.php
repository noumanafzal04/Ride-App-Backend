<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Billing\SubscriptionPlanResource;
use App\Http\Resources\Api\V1\Billing\SubscriptionResource;
use App\Models\FeatureOrder;
use App\Models\FeatureSetting;
use App\Models\ModuleBillingSetting;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\Billing\BillingService;
use App\Support\ApiResponse;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;

class AdminBillingController extends Controller
{
    public function __construct(protected BillingService $billing) {}

    // ── Plans ──
    public function plans()
    {
        $plans = SubscriptionPlan::orderBy('module')->orderBy('sort')->get();
        return SubscriptionPlanResource::collection($plans)->wrapWith('plans')->message('Plans.');
    }

    public function storePlan(Request $request)
    {
        $data = $this->planRules($request);
        $plan = SubscriptionPlan::create($data);
        return (new SubscriptionPlanResource($plan))->wrapWith('plan')->message('Plan created.')->status(201);
    }

    public function updatePlan(Request $request, int $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->update($this->planRules($request, false));
        return (new SubscriptionPlanResource($plan))->wrapWith('plan')->message('Plan updated.');
    }

    public function destroyPlan(int $id)
    {
        SubscriptionPlan::findOrFail($id)->delete();
        return ApiResponse::noContent('Plan deleted.');
    }

    protected function planRules(Request $request, bool $creating = true): array
    {
        return $request->validate([
            'module'        => [$creating ? 'required' : 'sometimes', 'in:ride,service,buysell,rental'],
            'name'          => [$creating ? 'required' : 'sometimes', 'string', 'max:60'],
            'duration_days' => [$creating ? 'required' : 'sometimes', 'integer', 'min:1', 'max:3650'],
            'post_limit'    => [$creating ? 'required' : 'sometimes', 'integer', 'min:1', 'max:100000'],
            'price'         => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'is_active'     => ['nullable', 'boolean'],
            'sort'          => ['nullable', 'integer'],
        ]);
    }

    // ── Module billing settings (free limit + enforcement) ──
    public function settings()
    {
        // Ensure all modules exist, then return them.
        foreach (\App\Constants\BillingModule::ALL as $m) {
            $this->billing->settings($m);
        }
        $rows = ModuleBillingSetting::orderBy('module')->get()->map(fn($s) => [
            'module'              => $s->module,
            'free_mode'           => $s->free_mode,
            'free_limit'          => $s->free_limit,
            'enforcement_enabled' => $s->enforcement_enabled,
        ]);
        return ApiResponse::success(['settings' => $rows], 'Billing settings.');
    }

    public function updateSetting(Request $request, string $module)
    {
        $data = $request->validate([
            'free_limit'          => ['required', 'integer', 'min:0', 'max:100000'],
            'enforcement_enabled' => ['required', 'boolean'],
        ]);
        $setting = $this->billing->settings($module);
        $setting->update($data);
        return ApiResponse::success(['setting' => [
            'module' => $setting->module, 'free_mode' => $setting->free_mode,
            'free_limit' => $setting->free_limit, 'enforcement_enabled' => $setting->enforcement_enabled,
        ]], 'Setting updated.');
    }

    // ── Subscriptions (list + manual grant) ──
    public function subscriptions(Request $request)
    {
        $subs = Subscription::with(['plan', 'user'])
            ->when($request->query('module'), fn($q, $m) => $q->where('module', $m))
            ->when($request->query('status'), fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        return SubscriptionResource::collection($subs)->wrapWith('subscriptions')->message('Subscriptions.');
    }

    public function grant(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'plan_id' => ['required', 'integer', 'exists:billing_plans,id'],
        ]);
        $plan = SubscriptionPlan::find($data['plan_id']);
        if (!$plan) {
            throw new ApiException('Plan not found.', 422);
        }
        $sub = $this->billing->activatePlan($data['user_id'], $plan, 'admin', 0);
        return (new SubscriptionResource($sub->load(['plan', 'user'])))->wrapWith('subscription')->message('Plan granted.')->status(201);
    }

    // ── Featured (paid boost) ──
    public function featureSettings()
    {
        $rows = FeatureSetting::orderBy('module')->get()->map(fn($s) => [
            'module'        => $s->module,
            'price'         => (float) $s->price,
            'duration_days' => (int) $s->duration_days,
            'is_active'     => (bool) $s->is_active,
        ]);
        return ApiResponse::success(['settings' => $rows], 'Feature settings.');
    }

    public function updateFeatureSetting(Request $request, string $module)
    {
        $data = $request->validate([
            'price'         => ['required', 'numeric', 'min:0', 'max:9999999'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'is_active'     => ['required', 'boolean'],
        ]);
        $setting = FeatureSetting::firstOrCreate(['module' => $module]);
        $setting->update($data);
        return ApiResponse::success(['setting' => [
            'module' => $setting->module, 'price' => (float) $setting->price,
            'duration_days' => (int) $setting->duration_days, 'is_active' => (bool) $setting->is_active,
        ]], 'Feature setting updated.');
    }

    public function featureOrders(Request $request)
    {
        $orders = FeatureOrder::with(['user', 'orderable'])
            ->when($request->query('module'), fn($q, $m) => $q->where('module', $m))
            ->latest()
            ->paginate((int) $request->query('per_page', 20));

        $orders->getCollection()->transform(function ($o) {
            $item = $o->orderable;
            $title = $item ? trim("{$item->make} {$item->model}" . ($item->year ? " {$item->year}" : '')) : 'Deleted item';
            return [
                'id'         => $o->id,
                'module'     => $o->module,
                'item'       => $title,
                'user'       => $o->user ? trim("{$o->user->first_name} {$o->user->last_name}") ?: 'User' : '—',
                'phone'      => $o->user?->phone_number,
                'amount'     => (float) $o->amount,
                'days'       => (int) $o->days,
                'status'     => $o->status,
                'paid_at'    => $o->paid_at?->toISOString(),
                'expires_at' => $o->expires_at?->toISOString(),
                'created_at' => $o->created_at?->toISOString(),
            ];
        });

        return ApiResponse::success([
            'orders' => $orders->items(),
            'meta'   => ['current_page' => $orders->currentPage(), 'last_page' => $orders->lastPage(), 'total' => $orders->total(), 'per_page' => $orders->perPage()],
        ], 'Feature orders.');
    }
}
