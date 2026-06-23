<?php

namespace App\Repositories\Marketplace;

use App\Models\CarListing;
use App\Repositories\BaseRepository;

class CarListingRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new CarListing();
    }

    protected array $browseRelations = [
        'images', 'city:id,name', 'seller:id,first_name,last_name,phone_number',
        'inspectionRequest:id,status,overall_grade,overall_score',
    ];

    // Public browse of ACTIVE listings, filtered + (optionally) ranked by proximity.
    public function paginatedBrowse(array $filters, ?float $nearLat = null, ?float $nearLng = null)
    {
        return $this->paginatedList(
            callback: function ($q) use ($filters, $nearLat, $nearLng) {
                $q->where('car_listings.status', CarListing::STATUS_ACTIVE);

                if (!empty($filters['q'])) {
                    $s = $filters['q'];
                    $q->where(function ($w) use ($s) {
                        $w->where('make', 'like', "%{$s}%")
                            ->orWhere('model', 'like', "%{$s}%")
                            ->orWhere('variant', 'like', "%{$s}%");
                    });
                }
                if (!empty($filters['make']))         $q->where('make', $filters['make']);
                if (!empty($filters['model']))        $q->where('model', $filters['model']);
                if (!empty($filters['city_id']))      $q->where('car_listings.city_id', $filters['city_id']);
                if (!empty($filters['transmission'])) $q->where('transmission', $filters['transmission']);
                if (!empty($filters['fuel_type']))    $q->where('fuel_type', $filters['fuel_type']);
                if (!empty($filters['condition']))    $q->where('condition', $filters['condition']);
                if (!empty($filters['listing_type'])) $q->where('listing_type', $filters['listing_type']);
                if (!empty($filters['year_min']))     $q->where('year', '>=', $filters['year_min']);
                if (!empty($filters['year_max']))     $q->where('year', '<=', $filters['year_max']);
                if (!empty($filters['price_min']))    $q->where('price', '>=', $filters['price_min']);
                if (!empty($filters['price_max']))    $q->where('price', '<=', $filters['price_max']);

                $sort = $filters['sort'] ?? null;
                if ($sort === 'price_asc')      { $q->orderBy('price'); }
                elseif ($sort === 'price_desc') { $q->orderByDesc('price'); }
                elseif ($sort === 'newest')     { $q->latest('car_listings.created_at'); }
                elseif ($nearLat !== null && $nearLng !== null && empty($filters['city_id'])) {
                    // Location-aware default: show ALL, nearest city first.
                    $q->leftJoin('cities', 'cities.id', '=', 'car_listings.city_id')
                        ->select('car_listings.*')
                        ->selectRaw(
                            '( 6371 * acos( least(1, greatest(-1,'
                            . ' cos(radians(?)) * cos(radians(cities.lat)) * cos(radians(cities.lon) - radians(?))'
                            . ' + sin(radians(?)) * sin(radians(cities.lat)) ))) ) AS distance_km',
                            [$nearLat, $nearLng, $nearLat]
                        )
                        ->orderByRaw('distance_km IS NULL')
                        ->orderBy('distance_km')
                        ->orderByDesc('car_listings.is_featured');
                } else {
                    $q->orderByDesc('is_featured')->latest('car_listings.created_at');
                }
            },
            relations: $this->browseRelations,
        );
    }

    public function findActiveWithRelations(int $id): ?CarListing
    {
        return $this->findOne(
            callback: fn($q) => $q->where('id', $id)->where('status', CarListing::STATUS_ACTIVE),
            relations: $this->browseRelations,
        );
    }

    // Single listing (any status) with relations — owner & admin views.
    public function findByIdWithRelations(int $id): ?CarListing
    {
        return $this->findOne(
            callback: fn($q) => $q->where('id', $id),
            relations: $this->browseRelations,
        );
    }

    // Admin queue — optional status + listing_type filters, newest first.
    public function adminPaginated(?string $status = null, ?string $type = null, ?int $limit = null)
    {
        return $this->paginatedList(
            callback: function ($q) use ($status, $type) {
                if ($status) $q->where('status', $status);
                if ($type)   $q->where('listing_type', $type);
                $q->latest();
            },
            relations: $this->browseRelations,
            limit: $limit,
        );
    }

    public function mine(int $userId)
    {
        return $this->paginatedList(
            callback: fn($q) => $q->where('user_id', $userId)->latest(),
            relations: $this->browseRelations,
        );
    }

    public function incrementViews(int $id): void
    {
        $this->model->newQuery()->where('id', $id)->increment('views_count');
    }
}
