<?php

namespace App\Http\Controllers\Api\V1\Ride;

use App\Actions\Driver\RidePostAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Driver\RidePostResource;
use Illuminate\Http\Request;

class RideController extends Controller
{
    public $resourceName = 'ride_post';

    public function __construct(protected RidePostAction $action) {}

    // Rider-facing browse of available ride posts
    public function index(Request $request)
    {
        $rides = $this->action->browse(auth()->id(), $request->all());

        return RidePostResource::collection($rides)
            ->wrapWith('ride_posts')
            ->message(__("{$this->resourceName}.all"));
    }

    // Rider-facing detail of a single ride post
    public function show(int $ridePostId)
    {
        $ride = $this->action->showForRider($ridePostId);

        return (new RidePostResource($ride))
            ->message(__("{$this->resourceName}.show"));
    }
}
