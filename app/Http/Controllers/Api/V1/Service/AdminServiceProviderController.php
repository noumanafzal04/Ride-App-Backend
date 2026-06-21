<?php

namespace App\Http\Controllers\Api\V1\Service;

use App\Actions\Service\ServiceProviderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Service\UpdateServiceProviderStatusRequest;
use App\Http\Resources\Api\V1\Service\ServiceProviderResource;
use Illuminate\Http\Request;

class AdminServiceProviderController extends Controller
{
    public function __construct(protected ServiceProviderAction $action) {}

    public function index(Request $request)
    {
        $items = $this->action->adminList($request->query('status'));

        return ServiceProviderResource::collection($items)
            ->wrapWith('providers')
            ->message('Service providers.');
    }

    public function setStatus(UpdateServiceProviderStatusRequest $request, int $id)
    {
        $provider = $this->action->setStatus($id, $request->validated()['status']);

        return (new ServiceProviderResource($provider))
            ->wrapWith('provider')
            ->message('Provider updated.');
    }
}
