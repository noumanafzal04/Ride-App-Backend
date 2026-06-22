<?php

namespace App\Http\Controllers\Api\V1\Service;

use App\Actions\Service\ServiceProviderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Service\StoreServiceProviderRequest;
use App\Http\Resources\Api\V1\Service\ServiceProviderResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ServiceProviderController extends Controller
{
    public function __construct(protected ServiceProviderAction $action) {}

    /** Browse approved providers (?category_id=&city_id=&near_lat=&near_lng=). */
    public function index(Request $request)
    {
        $items = $this->action->browse(
            $request->query('category_id') ? (int) $request->query('category_id') : null,
            $request->query('city_id') ? (int) $request->query('city_id') : null,
            $request->filled('near_lat') ? (float) $request->query('near_lat') : null,
            $request->filled('near_lng') ? (float) $request->query('near_lng') : null,
        );

        return ServiceProviderResource::collection($items)
            ->wrapWith('providers')
            ->message('Service providers.');
    }

    /** Public provider detail. */
    public function show(int $id)
    {
        return (new ServiceProviderResource($this->action->showPublic($id)))
            ->wrapWith('provider')
            ->message('Service provider.');
    }

    /** The current user's provider profile, or { provider: null } if not registered. */
    public function me()
    {
        $provider = $this->action->forUser(auth()->id());

        if (!$provider) {
            return ApiResponse::success(['provider' => null], 'No provider profile.');
        }

        return (new ServiceProviderResource($provider))
            ->wrapWith('provider')
            ->message('Your provider profile.');
    }

    /** Register as a service provider (created pending). */
    public function store(StoreServiceProviderRequest $request)
    {
        $provider = $this->action->register(auth()->id(), $request->validated());

        return (new ServiceProviderResource($provider))
            ->wrapWith('provider')
            ->message('Registration submitted — pending approval.')
            ->status(201);
    }
}
