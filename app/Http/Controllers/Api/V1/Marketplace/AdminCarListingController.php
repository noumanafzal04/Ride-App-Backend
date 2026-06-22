<?php

namespace App\Http\Controllers\Api\V1\Marketplace;

use App\Actions\Marketplace\CarListingAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Marketplace\CarListingResource;
use Illuminate\Http\Request;

class AdminCarListingController extends Controller
{
    public function __construct(protected CarListingAction $action) {}

    // ?status=&type=
    public function index(Request $request)
    {
        $items = $this->action->adminList($request->query('status'), $request->query('type'));

        return CarListingResource::collection($items)
            ->wrapWith('listings')
            ->message('Car listings.');
    }

    public function show(int $id)
    {
        return (new CarListingResource($this->action->adminFind($id)))
            ->wrapWith('listing')
            ->message('Listing detail.');
    }

    public function setStatus(Request $request, int $id)
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,rejected,sold,inactive,pending'],
        ]);

        return (new CarListingResource($this->action->setStatus($id, $data['status'])))
            ->wrapWith('listing')
            ->message('Listing status updated.');
    }

    public function setPrice(Request $request, int $id)
    {
        $data = $request->validate([
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
        ]);

        return (new CarListingResource($this->action->setPrice($id, (float) $data['price'])))
            ->wrapWith('listing')
            ->message('Price updated.');
    }

    public function setFeatured(Request $request, int $id)
    {
        $data = $request->validate([
            'is_featured' => ['required', 'boolean'],
        ]);

        return (new CarListingResource($this->action->setFeatured($id, (bool) $data['is_featured'])))
            ->wrapWith('listing')
            ->message('Listing updated.');
    }
}
