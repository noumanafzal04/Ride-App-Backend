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

    // "Currently featured" = flag set AND paid window not expired. Used to float featured to the top.
    private const FEATURED_FIRST = '(car_listings.is_featured = 1 and (car_listings.featured_until is null or car_listings.featured_until >= now())) desc';

    // Free-text search: every term must match SOME searchable field (AND across terms, OR across fields).
    private function applySearch($q, string $raw): void
    {
        foreach (preg_split('/\s+/', trim($raw)) as $term) {
            if ($term === '') continue;
            $like = '%' . $term . '%';
            $q->where(function ($w) use ($like) {
                $w->where('make', 'like', $like)
                    ->orWhere('model', 'like', $like)
                    ->orWhere('variant', 'like', $like)
                    ->orWhere('color', 'like', $like)
                    ->orWhere('condition', 'like', $like)
                    ->orWhere('transmission', 'like', $like)
                    ->orWhere('fuel_type', 'like', $like)
                    ->orWhere('area', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('year', 'like', $like)
                    ->orWhereHas('city', fn($c) => $c->where('name', 'like', $like));
            });
        }
    }

    // Public browse of ACTIVE listings, filtered + (optionally) ranked by proximity.
    public function paginatedBrowse(array $filters, ?float $nearLat = null, ?float $nearLng = null)
    {
        return $this->paginatedList(
            callback: function ($q) use ($filters, $nearLat, $nearLng) {
                $q->where('car_listings.status', CarListing::STATUS_ACTIVE);

                if (!empty($filters['q'])) {
                    $this->applySearch($q, $filters['q']);
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
                elseif ($sort === 'newest')     { $q->orderByRaw(self::FEATURED_FIRST)->latest('car_listings.created_at'); }
                elseif ($nearLat !== null && $nearLng !== null && empty($filters['city_id'])) {
                    // Location-aware default: featured first, then nearest city.
                    $q->leftJoin('cities', 'cities.id', '=', 'car_listings.city_id')
                        ->select('car_listings.*')
                        ->selectRaw(
                            '( 6371 * acos( least(1, greatest(-1,'
                            . ' cos(radians(?)) * cos(radians(cities.lat)) * cos(radians(cities.lon) - radians(?))'
                            . ' + sin(radians(?)) * sin(radians(cities.lat)) ))) ) AS distance_km',
                            [$nearLat, $nearLng, $nearLat]
                        )
                        ->orderByRaw(self::FEATURED_FIRST)
                        ->orderByRaw('distance_km IS NULL')
                        ->orderBy('distance_km');
                } else {
                    $q->orderByRaw(self::FEATURED_FIRST)->latest('car_listings.created_at');
                }
            },
            relations: $this->browseRelations,
        );
    }

    // Typeahead suggestions — top matching active listings (featured first).
    public function searchSuggestions(string $raw, int $limit = 8)
    {
        return $this->model->newQuery()
            ->where('status', CarListing::STATUS_ACTIVE)
            ->when(trim($raw) !== '', fn($q) => $this->applySearch($q, $raw))
            ->with(['images', 'city:id,name'])
            ->orderByRaw(self::FEATURED_FIRST)
            ->latest('created_at')
            ->limit($limit)
            ->get();
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

    // Public seller profile — their live + sold listings (active first, sold after).
    public function sellerListings(int $userId)
    {
        return $this->list(
            callback: fn($q) => $q->where('user_id', $userId)
                ->whereIn('status', [CarListing::STATUS_ACTIVE, CarListing::STATUS_SOLD])
                ->orderByRaw("status = '" . CarListing::STATUS_SOLD . "'") // active (0) before sold (1)
                ->orderByDesc('is_featured')
                ->latest(),
            relations: $this->browseRelations,
        );
    }

    public function incrementViews(int $id): void
    {
        $this->model->newQuery()->where('id', $id)->increment('views_count');
    }
}
