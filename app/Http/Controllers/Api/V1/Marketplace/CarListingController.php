<?php

namespace App\Http\Controllers\Api\V1\Marketplace;

use App\Actions\Marketplace\CarListingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketplace\StoreCarListingRequest;
use App\Http\Requests\Marketplace\UpdateCarListingRequest;
use App\Http\Resources\Api\V1\Marketplace\CarListingResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class CarListingController extends Controller
{
    public function __construct(protected CarListingAction $action) {}

    // Browse active listings with filters (?q=&make=&city_id=&price_min=&...&near_lat=&near_lng=)
    public function index(Request $request)
    {
        $filters = $request->only([
            'q', 'make', 'model', 'city_id', 'transmission', 'fuel_type',
            'condition', 'listing_type', 'year_min', 'year_max', 'price_min', 'price_max', 'sort',
        ]);

        $items = $this->action->browse(
            array_filter($filters, fn($v) => $v !== null && $v !== ''),
            $request->filled('near_lat') ? (float) $request->query('near_lat') : null,
            $request->filled('near_lng') ? (float) $request->query('near_lng') : null,
        );

        return CarListingResource::collection($items)
            ->wrapWith('listings')
            ->message('Car listings.');
    }

    // The current user's own listings (any status).
    public function mine()
    {
        $items = $this->action->mine(auth()->id());

        return CarListingResource::collection($items)
            ->wrapWith('listings')
            ->message('Your listings.');
    }

    public function show(int $id)
    {
        return (new CarListingResource($this->action->show($id)))
            ->wrapWith('listing')
            ->message('Listing detail.');
    }

    // Typeahead suggestions for the search box.
    public function suggest(Request $request)
    {
        $items = $this->action->suggest((string) $request->query('q', ''), (int) $request->query('limit', 8));

        return CarListingResource::collection($items)
            ->wrapWith('listings')
            ->message('Suggestions.');
    }

    // Public seller profile: their live + sold listings (seller info in data.meta.seller).
    public function seller(int $userId)
    {
        $data = $this->action->sellerProfile($userId);

        return CarListingResource::collection($data['listings'])
            ->wrapWith('listings')
            ->withMeta(['seller' => $data['seller']])
            ->message('Seller profile.');
    }

    public function store(StoreCarListingRequest $request)
    {
        $listing = $this->action->create(
            auth()->id(),
            $request->validated(),
            $request->file('images') ?? [],
        );

        return (new CarListingResource($listing))
            ->wrapWith('listing')
            ->message('Listing posted.')
            ->status(201);
    }

    public function update(UpdateCarListingRequest $request, int $id)
    {
        $listing = $this->action->update(auth()->id(), $id, $request->validated());

        return (new CarListingResource($listing))
            ->wrapWith('listing')
            ->message('Listing updated.');
    }

    public function markSold(int $id)
    {
        $listing = $this->action->markSold(auth()->id(), $id);

        return (new CarListingResource($listing))
            ->wrapWith('listing')
            ->message('Marked as sold.');
    }

    // Feature pricing (price + duration) for the buy/sell module.
    public function featurePricing()
    {
        return ApiResponse::success($this->action->featurePricing(), 'Feature pricing.');
    }

    // Owner pays to feature their listing (marked paid instantly for now).
    public function feature(int $id)
    {
        $listing = $this->action->feature(auth()->id(), $id);

        return (new CarListingResource($listing))
            ->wrapWith('listing')
            ->message('Your listing is now featured.');
    }

    public function destroy(int $id)
    {
        $this->action->destroy(auth()->id(), $id);
        return ApiResponse::noContent();
    }
}
