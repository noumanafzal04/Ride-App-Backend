<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Actions\Driver\DriverPublicAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Driver\DriverTripResource;
use App\Http\Resources\Api\V1\Driver\ReceivedReviewResource;
use App\Support\ApiResponse;

class DriverPublicController extends Controller
{
    public function __construct(protected DriverPublicAction $action) {}

    // Aggregate stats (rating, total trips, review count)
    public function summary(int $driverId)
    {
        return ApiResponse::success($this->action->summary($driverId));
    }

    // Paginated reviews received by the driver
    public function reviews(int $driverId)
    {
        return ReceivedReviewResource::collection($this->action->reviews($driverId))
            ->wrapWith('reviews')
            ->message('Success');
    }

    // Paginated completed trips
    public function trips(int $driverId)
    {
        return DriverTripResource::collection($this->action->trips($driverId))
            ->wrapWith('trips')
            ->message('Success');
    }
}
