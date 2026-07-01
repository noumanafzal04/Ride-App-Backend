<?php

namespace App\Actions\Rental;

use App\Constants\BillingModule;
use App\Exceptions\ApiException;
use App\Models\InspectionRequest;
use App\Models\RentalCar;
use App\Repositories\Rental\RentalCarRepository;
use App\Services\Billing\BillingService;
use App\Services\Media\ImageUploadService;
use App\Services\Notification\AdminNotificationService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;

class RentalCarAction
{
    public function __construct(
        protected RentalCarRepository $repository,
        protected ImageUploadService $images,
        protected AdminNotificationService $adminNotifications,
        protected NotificationService $notifications,
        protected BillingService $billing,
        protected \App\Services\Feature\FeatureService $feature,
    ) {}

    /** Owner pays to feature their own rental (see FeatureService). */
    public function feature(int $userId, int $id): RentalCar
    {
        $car = $this->ownedOrFail($userId, $id);
        $this->feature->purchase($userId, $car, \App\Services\Feature\FeatureService::MODULE_RENTAL);
        return $this->repository->findByIdWithRelations($id);
    }

    public function featurePricing(): array
    {
        return $this->feature->pricing(\App\Services\Feature\FeatureService::MODULE_RENTAL);
    }

    public function browse(array $filters, ?float $nearLat = null, ?float $nearLng = null)
    {
        return $this->repository->paginatedBrowse($filters, $nearLat, $nearLng);
    }

    public function models()
    {
        return $this->repository->distinctModels();
    }

    public function show(int $id): RentalCar
    {
        $car = $this->repository->findActiveWithRelations($id);
        if (!$car) throw new ApiException('Rental car not found.', 404);
        $this->repository->incrementViews($id);
        return $car;
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

    public function create(int $userId, array $data, array $imageFiles = []): RentalCar
    {
        $isManaged = ($data['listing_type'] ?? RentalCar::TYPE_SELF) === RentalCar::TYPE_MANAGED;

        // Managed ("EZRide lists it for you") requests are free leads — skip the paywall.
        $gate = $isManaged ? null : $this->billing->assertCanPost($userId, BillingModule::RENTAL);

        return DB::transaction(function () use ($userId, $data, $imageFiles, $gate, $isManaged) {
            $type = $isManaged ? RentalCar::TYPE_MANAGED : RentalCar::TYPE_SELF;
            $status = $type === RentalCar::TYPE_MANAGED ? RentalCar::STATUS_PENDING : RentalCar::STATUS_ACTIVE;

            $car = $this->repository->create([
                'user_id'               => $userId,
                'listing_type'          => $type,
                'status'                => $status,
                'make'                  => $data['make'],
                'model'                 => $data['model'],
                'variant'               => $data['variant'] ?? null,
                'year'                  => $data['year'],
                'category'              => $data['category'] ?? null,
                'seats'                 => $data['seats'] ?? null,
                'transmission'          => $data['transmission'] ?? null,
                'fuel_type'             => $data['fuel_type'] ?? null,
                'color'                 => $data['color'] ?? null,
                'rental_type'           => $data['rental_type'] ?? 'with_driver',
                'price_per_day'         => $data['price_per_day'] ?? null,
                'price_per_day_self'    => $data['price_per_day_self'] ?? null,
                'deposit'               => $data['deposit'] ?? null,
                'min_days'              => $data['min_days'] ?? 1,
                'city_id'               => $data['city_id'] ?? null,
                'area'                  => $data['area'] ?? null,
                'description'           => $data['description'] ?? null,
                'features'              => $data['features'] ?? null,
                'inspection_request_id' => $this->resolveInspectionId($userId, $data['inspection_request_id'] ?? null),
            ]);

            foreach (array_values($imageFiles) as $i => $file) {
                $path = $this->images->upload(file: $file, folder: 'rentals');
                $car->images()->create(['path' => $path, 'sort' => $i, 'is_primary' => $i === 0]);
            }

            if ($type === RentalCar::TYPE_MANAGED) {
                $this->adminNotifications->push('rental_managed_new', 'New managed rental',
                    "{$car->make} {$car->model} submitted for EZRide to manage.", ['rental_car_id' => $car->id]);
            }

            if (!$isManaged) {
                $this->billing->consume($userId, BillingModule::RENTAL, $gate);
            }

            return $this->repository->findByIdWithRelations($car->id);
        });
    }

    public function update(int $userId, int $id, array $data): RentalCar
    {
        $car = $this->ownedOrFail($userId, $id);
        $payload = collect($data)->only([
            'make', 'model', 'variant', 'year', 'category', 'seats', 'transmission', 'fuel_type', 'color',
            'rental_type', 'price_per_day', 'price_per_day_self', 'deposit', 'min_days',
            'city_id', 'area', 'description', 'features',
        ])->toArray();
        $this->repository->update($car->id, $payload);
        return $this->repository->findByIdWithRelations($car->id);
    }

    // Owner pause / reactivate / remove
    public function setOwnerStatus(int $userId, int $id, string $status): RentalCar
    {
        $car = $this->ownedOrFail($userId, $id);
        if (!in_array($status, [RentalCar::STATUS_ACTIVE, RentalCar::STATUS_PAUSED], true)) {
            throw new ApiException('Invalid status.', 422);
        }
        $this->repository->update($car->id, ['status' => $status]);
        return $this->repository->findByIdWithRelations($car->id);
    }

    public function destroy(int $userId, int $id): void
    {
        $car = $this->ownedOrFail($userId, $id);
        DB::transaction(function () use ($car) {
            foreach ($car->images as $img) $this->images->delete($img->path);
            $this->repository->deleteById($car->id);
        });
    }

    // ── Admin ──
    public function adminList(?string $status, ?string $type, ?int $perPage = null)
    {
        return $this->repository->adminPaginated($status, $type, $perPage);
    }

    public function adminFind(int $id): RentalCar
    {
        $car = $this->repository->findByIdWithRelations($id);
        if (!$car) throw new ApiException('Rental car not found.', 404);
        return $car;
    }

    public function setStatus(int $id, string $status): RentalCar
    {
        $car = $this->adminFind($id);
        $this->repository->update($id, ['status' => $status]);
        if ($car->user_id) {
            if ($status === RentalCar::STATUS_ACTIVE && $car->status !== RentalCar::STATUS_ACTIVE) {
                $this->notifications->push($car->user_id, 'rental_approved', 'Rental approved',
                    "Your {$car->make} {$car->model} is now live for rent.", ['rental_car_id' => $id]);
            } elseif ($status === RentalCar::STATUS_REJECTED) {
                $this->notifications->push($car->user_id, 'rental_rejected', 'Rental not approved',
                    "Your {$car->make} {$car->model} rental was not approved.", ['rental_car_id' => $id]);
            }
        }
        return $this->repository->findByIdWithRelations($id);
    }

    public function setPrice(int $id, float $price): RentalCar
    {
        $car = $this->adminFind($id);
        $this->repository->update($id, ['price_per_day' => $price]);
        if ($car->user_id) {
            $this->notifications->push($car->user_id, 'rental_priced', 'Price set by EZRide',
                "EZRide set the daily price for your {$car->make} {$car->model}.", ['rental_car_id' => $id]);
        }
        return $this->repository->findByIdWithRelations($id);
    }

    public function setFeatured(int $id, bool $featured): RentalCar
    {
        $this->adminFind($id);
        $this->repository->update($id, ['is_featured' => $featured]);
        return $this->repository->findByIdWithRelations($id);
    }

    protected function ownedOrFail(int $userId, int $id): RentalCar
    {
        $car = $this->repository->findByIdWithRelations($id);
        if (!$car) throw new ApiException('Rental car not found.', 404);
        if ((int) $car->user_id !== (int) $userId) throw new ApiException('You do not own this rental.', 403);
        return $car;
    }

    protected function resolveInspectionId(int $userId, $inspectionId): ?int
    {
        if (!$inspectionId) return null;
        $insp = InspectionRequest::where('id', $inspectionId)->where('user_id', $userId)->where('status', 'completed')->first();
        if (!$insp) throw new ApiException('Selected inspection is not valid or not completed.', 422);
        return (int) $insp->id;
    }
}
