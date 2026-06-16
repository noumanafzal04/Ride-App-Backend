<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Actions\Driver\RidePostAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Driver\RidePostRequest;
use App\Http\Resources\Api\V1\Driver\RidePostResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class RidePostController extends Controller
{
    public $resourceName = 'ride_post';

    public function __construct(protected RidePostAction $action) {}

    public function index(Request $request)
    {
        $posts = $this->action->all(auth()->id(), $request->all());

        return RidePostResource::collection($posts)
            ->wrapWith('ride_posts')
            ->message(__("{$this->resourceName}.all"));
    }

    public function store(RidePostRequest $request)
    {
        $post = $this->action->create(auth()->id(), $request->validated());
        return (new RidePostResource($post))
            ->message(__("{$this->resourceName}.created"))
            ->status(201);
    }

    public function show(int $id)
    {
        $post = $this->action->show(auth()->id(), $id);

        return (new RidePostResource($post))
            ->message(__("{$this->resourceName}.show"));
    }

    public function update(RidePostRequest $request, int $id)
    {
        $this->action->update(auth()->id(), $id, $request->validated());

        return ApiResponse::noContent(__("{$this->resourceName}.updated"));
    }

    public function destroy(int $id)
    {
        $this->action->destroy(auth()->id(), $id);

        return ApiResponse::noContent(__("{$this->resourceName}.deleted"));
    }
}
