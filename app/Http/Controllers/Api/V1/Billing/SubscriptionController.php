<?php

namespace App\Http\Controllers\Api\V1\Billing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Billing\SubscriptionPlanResource;
use App\Http\Resources\Api\V1\Billing\SubscriptionResource;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\Billing\BillingService;
use App\Support\ApiResponse;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(protected BillingService $billing) {}

    // Active plans the user can buy (optionally for one module).
    public function plans(Request $request)
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->when($request->query('module'), fn($q, $m) => $q->where('module', $m))
            ->orderBy('module')->orderBy('sort')->get();

        return SubscriptionPlanResource::collection($plans)->wrapWith('plans')->message('Plans.');
    }

    // The user's billing status across modules + their active subscriptions.
    public function me()
    {
        $userId = auth()->id();
        $subs = Subscription::with('plan')->where('user_id', $userId)
            ->where('status', Subscription::STATUS_ACTIVE)->where('ends_at', '>', now())->get();

        return ApiResponse::success([
            'modules'       => $this->billing->statusFor($userId),
            'subscriptions' => SubscriptionResource::collection($subs)->resolve(),
        ], 'Subscription status.');
    }

    // Self-activate a plan (no payment gateway yet — records the subscription).
    public function subscribe(Request $request)
    {
        $data = $request->validate(['plan_id' => ['required', 'integer', 'exists:billing_plans,id']]);
        $plan = SubscriptionPlan::where('id', $data['plan_id'])->where('is_active', true)->first();
        if (!$plan) {
            throw new ApiException('Plan not available.', 422);
        }

        $sub = $this->billing->activatePlan(auth()->id(), $plan, 'self');

        return (new SubscriptionResource($sub->load('plan')))->wrapWith('subscription')->message('Subscribed.')->status(201);
    }
}
