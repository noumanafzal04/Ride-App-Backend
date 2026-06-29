<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Driver\RidePostAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Admin\AdminRideResource;
use App\Repositories\Driver\RidePostRepository;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class AdminRideController extends Controller
{
    public function __construct(
        protected RidePostRepository $repository,
        protected RidePostAction $action,
    ) {}

    public function index(Request $request)
    {
        $filters = array_filter(
            $request->only(['status', 'city_id', 'search']),
            fn($v) => $v !== null && $v !== '',
        );

        $items = $this->repository->paginatedForAdmin($filters, (int) $request->query('per_page', 15));

        return AdminRideResource::collection($items)->wrapWith('rides')->message('Rides.');
    }

    public function stats()
    {
        return ApiResponse::success($this->repository->adminStats(), 'Ride stats.');
    }

    public function cancel(int $id)
    {
        $this->action->adminCancel($id);

        return ApiResponse::success(null, 'Ride cancelled.');
    }
}
