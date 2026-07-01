<?php

namespace App\Actions\Marketplace;

use App\Exceptions\ApiException;
use App\Models\CarListing;
use App\Models\InspectionRequest;
use App\Repositories\Marketplace\CarListingRepository;
use App\Services\Media\ImageUploadService;
use App\Services\Notification\AdminNotificationService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;

class CarListingAction
{
    public function __construct(
        protected CarListingRepository $repository,
        protected ImageUploadService $images,
        protected AdminNotificationService $adminNotifications,
        protected NotificationService $notifications,
        protected \App\Services\Billing\BillingService $billing,
        protected \App\Services\Feature\FeatureService $feature,
    ) {}

    /** Owner pays to feature their own listing (see FeatureService). */
    public function feature(int $userId, int $id): CarListing
    {
        $listing = $this->ownedOrFail($userId, $id);
        if ($listing->status === CarListing::STATUS_SOLD) {
            throw new ApiException('This listing is already sold.', 422);
        }
        $this->feature->purchase($userId, $listing, \App\Services\Feature\FeatureService::MODULE_BUYSELL);
        return $this->repository->findByIdWithRelations($id);
    }

    public function featurePricing(): array
    {
        return $this->feature->pricing(\App\Services\Feature\FeatureService::MODULE_BUYSELL);
    }

    public function browse(array $filters, ?float $nearLat = null, ?float $nearLng = null)
    {
        return $this->repository->paginatedBrowse($filters, $nearLat, $nearLng);
    }

    public function show(int $id): CarListing
    {
        $listing = $this->repository->findActiveWithRelations($id);
        if (!$listing) {
            throw new ApiException('Listing not found.', 404);
        }
        $this->repository->incrementViews($id);
        return $listing;
    }

    public function mine(int $userId)
    {
        return $this->repository->mine($userId);
    }

    /** Typeahead suggestions for the search box. */
    public function suggest(string $q, int $limit = 8)
    {
        return $this->repository->searchSuggestions($q, $limit);
    }

    /** Public seller profile: seller info + their live/sold listings. */
    public function sellerProfile(int $userId): array
    {
        $seller = \App\Models\User::query()
            ->select('id', 'first_name', 'last_name', 'phone_number', 'created_at')
            ->find($userId);

        if (!$seller) {
            throw new ApiException('Seller not found.', 404);
        }

        $listings = $this->repository->sellerListings($userId);

        return [
            'seller' => [
                'id'           => $seller->id,
                'name'         => trim("{$seller->first_name} {$seller->last_name}") ?: 'Seller',
                'phone'        => $seller->phone_number,
                'member_since' => $seller->created_at?->toISOString(),
                'active_count' => $listings->where('status', CarListing::STATUS_ACTIVE)->count(),
                'sold_count'   => $listings->where('status', CarListing::STATUS_SOLD)->count(),
            ],
            'listings' => $listings,
        ];
    }

    public function create(int $userId, array $data, array $imageFiles = []): CarListing
    {
        $isManaged = ($data['listing_type'] ?? CarListing::TYPE_SELF) === CarListing::TYPE_MANAGED;

        // Managed ("EZRide sells it for you") requests are free leads — skip the paywall.
        // Self listings hit the subscription gate (free while billing is disabled for buy/sell).
        $gate = $isManaged ? null : $this->billing->assertCanPost($userId, \App\Constants\BillingModule::BUYSELL);

        return DB::transaction(function () use ($userId, $data, $imageFiles, $gate, $isManaged) {
            $type = $isManaged ? CarListing::TYPE_MANAGED : CarListing::TYPE_SELF;

            // Self listings go live instantly; EZRide-managed go to review.
            $status = $type === CarListing::TYPE_MANAGED ? CarListing::STATUS_PENDING : CarListing::STATUS_ACTIVE;

            $inspectionId = $this->resolveInspectionId($userId, $data['inspection_request_id'] ?? null);

            $listing = $this->repository->create([
                'user_id'               => $userId,
                'listing_type'          => $type,
                'status'                => $status,
                'make'                  => $data['make'],
                'model'                 => $data['model'],
                'variant'               => $data['variant'] ?? null,
                'year'                  => $data['year'],
                'price'                 => $data['price'] ?? null,
                'mileage'               => $data['mileage'] ?? null,
                'condition'             => $data['condition'] ?? 'used',
                'transmission'          => $data['transmission'] ?? null,
                'fuel_type'             => $data['fuel_type'] ?? null,
                'engine_cc'             => $data['engine_cc'] ?? null,
                'color'                 => $data['color'] ?? null,
                'city_id'               => $data['city_id'] ?? null,
                'area'                  => $data['area'] ?? null,
                'description'           => $data['description'] ?? null,
                'features'              => $data['features'] ?? null,
                'inspection_request_id' => $inspectionId,
            ]);

            foreach (array_values($imageFiles) as $i => $file) {
                $path = $this->images->upload(file: $file, folder: 'listings');
                $listing->images()->create([
                    'path'       => $path,
                    'sort'       => $i,
                    'is_primary' => $i === 0,
                ]);
            }

            if ($type === CarListing::TYPE_MANAGED) {
                $this->adminNotifications->push(
                    'listing_managed_new',
                    'New managed-sale request',
                    "{$listing->make} {$listing->model} ({$listing->year}) submitted for EZRide to sell.",
                    ['car_listing_id' => $listing->id],
                );
            }

            if (!$isManaged) {
                $this->billing->consume($userId, \App\Constants\BillingModule::BUYSELL, $gate);
            }

            return $this->repository->findByIdWithRelations($listing->id);
        });
    }

    public function update(int $userId, int $id, array $data): CarListing
    {
        $listing = $this->ownedOrFail($userId, $id);

        $payload = collect($data)->only([
            'make', 'model', 'variant', 'year', 'price', 'mileage', 'condition',
            'transmission', 'fuel_type', 'engine_cc', 'color', 'city_id', 'area',
            'description', 'features',
        ])->toArray();

        if (array_key_exists('inspection_request_id', $data)) {
            $payload['inspection_request_id'] = $this->resolveInspectionId($userId, $data['inspection_request_id']);
        }

        $this->repository->update($listing->id, $payload);
        return $this->repository->findByIdWithRelations($listing->id);
    }

    public function markSold(int $userId, int $id): CarListing
    {
        $listing = $this->ownedOrFail($userId, $id);
        $this->repository->update($listing->id, ['status' => CarListing::STATUS_SOLD, 'sold_at' => now()]);
        return $this->repository->findByIdWithRelations($listing->id);
    }

    public function destroy(int $userId, int $id): void
    {
        $listing = $this->ownedOrFail($userId, $id);
        DB::transaction(function () use ($listing) {
            foreach ($listing->images as $img) {
                $this->images->delete($img->path);
            }
            $this->repository->deleteById($listing->id);
        });
    }

    // ─── Admin (portal) ───────────────────────────────────────────────

    public function adminList(?string $status = null, ?string $type = null, ?int $perPage = null)
    {
        return $this->repository->adminPaginated($status, $type, $perPage);
    }

    public function adminFind(int $id): CarListing
    {
        $listing = $this->repository->findByIdWithRelations($id);
        if (!$listing) {
            throw new ApiException('Listing not found.', 404);
        }
        return $listing;
    }

    public function setStatus(int $id, string $status): CarListing
    {
        $listing = $this->adminFind($id);

        $data = ['status' => $status];
        if ($status === CarListing::STATUS_SOLD) {
            $data['sold_at'] = now();
        }
        $this->repository->update($id, $data);

        if ($listing->user_id) {
            if ($status === CarListing::STATUS_ACTIVE && $listing->status !== CarListing::STATUS_ACTIVE) {
                $this->notifications->push($listing->user_id, 'listing_approved', 'Listing approved',
                    "Your {$listing->make} {$listing->model} is now live on the marketplace.", ['car_listing_id' => $id]);
            } elseif ($status === CarListing::STATUS_REJECTED) {
                $this->notifications->push($listing->user_id, 'listing_rejected', 'Listing not approved',
                    "Your {$listing->make} {$listing->model} listing was not approved.", ['car_listing_id' => $id]);
            }
        }

        return $this->repository->findByIdWithRelations($id);
    }

    public function setPrice(int $id, float $price): CarListing
    {
        $listing = $this->adminFind($id);
        $this->repository->update($id, ['price' => $price]);

        if ($listing->user_id) {
            $this->notifications->push($listing->user_id, 'listing_priced', 'Price set by EZRide',
                "EZRide set the price for your {$listing->make} {$listing->model}.", ['car_listing_id' => $id]);
        }

        return $this->repository->findByIdWithRelations($id);
    }

    public function setFeatured(int $id, bool $featured): CarListing
    {
        $this->adminFind($id);
        $this->repository->update($id, ['is_featured' => $featured]);
        return $this->repository->findByIdWithRelations($id);
    }

    protected function ownedOrFail(int $userId, int $id): CarListing
    {
        $listing = $this->repository->findByIdWithRelations($id);
        if (!$listing) {
            throw new ApiException('Listing not found.', 404);
        }
        if ((int) $listing->user_id !== (int) $userId) {
            throw new ApiException('You do not own this listing.', 403);
        }
        return $listing;
    }

    // A linked inspection must belong to the seller and be completed.
    protected function resolveInspectionId(int $userId, $inspectionId): ?int
    {
        if (!$inspectionId) return null;

        $insp = InspectionRequest::query()
            ->where('id', $inspectionId)
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->first();

        if (!$insp) {
            throw new ApiException('Selected inspection is not valid or not completed.', 422);
        }
        return (int) $insp->id;
    }
}
