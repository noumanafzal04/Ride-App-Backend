<?php

namespace App\Repositories\Rental;

use App\Models\RentalCar;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class RentalCarRepository extends BaseRepository
{
    // Subquery: average rating the car owner has received.
    private const OWNER_RATING_SQL = '(select avg(rating) from ratings where ratings.to_user_id = rental_cars.user_id)';

    public function __construct()
    {
        $this->model = new RentalCar();
    }

    protected array $relations = [
        'images', 'city:id,name', 'owner:id,first_name,last_name,phone_number',
        'inspectionRequest:id,status,overall_grade,overall_score',
    ];

    // "Currently featured" = flag set AND paid window not expired. Floats featured to the top.
    private const FEATURED_FIRST = '(rental_cars.is_featured = 1 and (rental_cars.featured_until is null or rental_cars.featured_until >= now())) desc';

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
                    ->orWhere('category', 'like', $like)
                    ->orWhere('transmission', 'like', $like)
                    ->orWhere('fuel_type', 'like', $like)
                    ->orWhere('area', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('year', 'like', $like)
                    ->orWhereHas('city', fn($c) => $c->where('name', 'like', $like));
            });
        }
    }

    public function paginatedBrowse(array $filters, ?float $nearLat = null, ?float $nearLng = null)
    {
        return $this->paginatedList(
            callback: function ($q) use ($filters, $nearLat, $nearLng) {
                $q->where('rental_cars.status', RentalCar::STATUS_ACTIVE)
                    ->select('rental_cars.*')
                    ->addSelect(DB::raw(self::OWNER_RATING_SQL . ' as owner_rating'));

                if (!empty($filters['q'])) {
                    $this->applySearch($q, $filters['q']);
                }
                if (!empty($filters['category']))    $q->where('category', $filters['category']);
                if (!empty($filters['city_id']))     $q->where('rental_cars.city_id', $filters['city_id']);
                if (!empty($filters['make']))        $q->where('make', $filters['make']);
                if (!empty($filters['model']))       $q->where('model', $filters['model']);
                if (!empty($filters['rental_type'])) {
                    $rt = $filters['rental_type'];
                    $q->where(fn($w) => $w->where('rental_type', $rt)->orWhere('rental_type', 'both'));
                }
                if (!empty($filters['transmission'])) $q->where('transmission', $filters['transmission']);
                if (!empty($filters['price_min']))    $q->where('price_per_day', '>=', $filters['price_min']);
                if (!empty($filters['price_max']))    $q->where('price_per_day', '<=', $filters['price_max']);
                if (!empty($filters['rating_min']))   $q->whereRaw(self::OWNER_RATING_SQL . ' >= ?', [$filters['rating_min']]);

                $sort = $filters['sort'] ?? null;
                if ($sort === 'price_asc')      { $q->orderBy('price_per_day'); }
                elseif ($sort === 'price_desc') { $q->orderByDesc('price_per_day'); }
                elseif ($sort === 'rating')     { $q->orderByRaw('owner_rating IS NULL')->orderByDesc('owner_rating'); }
                elseif ($nearLat !== null && $nearLng !== null && empty($filters['city_id'])) {
                    // Featured first, then nearest city.
                    $q->leftJoin('cities', 'cities.id', '=', 'rental_cars.city_id')
                        ->addSelect(DB::raw(
                            '( 6371 * acos( least(1, greatest(-1,'
                            . ' cos(radians(' . (float) $nearLat . ')) * cos(radians(cities.lat)) * cos(radians(cities.lon) - radians(' . (float) $nearLng . '))'
                            . ' + sin(radians(' . (float) $nearLat . ')) * sin(radians(cities.lat)) ))) ) AS distance_km'
                        ))
                        ->orderByRaw(self::FEATURED_FIRST)
                        ->orderByRaw('distance_km IS NULL')
                        ->orderBy('distance_km');
                } else {
                    $q->orderByRaw(self::FEATURED_FIRST)->latest('rental_cars.created_at');
                }
            },
            relations: $this->relations,
        );
    }

    public function findActiveWithRelations(int $id): ?RentalCar
    {
        return $this->findOne(
            callback: fn($q) => $q->where('id', $id)->where('status', RentalCar::STATUS_ACTIVE)
                ->select('rental_cars.*')
                ->addSelect(DB::raw(self::OWNER_RATING_SQL . ' as owner_rating')),
            relations: $this->relations,
        );
    }

    // Typeahead suggestions — top matching active rentals (featured first).
    public function searchSuggestions(string $raw, int $limit = 8)
    {
        return $this->model->newQuery()
            ->where('status', RentalCar::STATUS_ACTIVE)
            ->select('rental_cars.*')
            ->addSelect(DB::raw(self::OWNER_RATING_SQL . ' as owner_rating'))
            ->when(trim($raw) !== '', fn($q) => $this->applySearch($q, $raw))
            ->with(['images', 'city:id,name'])
            ->orderByRaw(self::FEATURED_FIRST)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /** Distinct make + model combos among active listings (for the model filter). */
    public function distinctModels()
    {
        return $this->model->newQuery()
            ->where('status', RentalCar::STATUS_ACTIVE)
            ->select('make', 'model')
            ->selectRaw('count(*) as count')
            ->groupBy('make', 'model')
            ->orderBy('make')->orderBy('model')
            ->get();
    }

    public function findByIdWithRelations(int $id): ?RentalCar
    {
        return $this->findOne(callback: fn($q) => $q->where('id', $id), relations: $this->relations);
    }

    public function mine(int $userId)
    {
        return $this->paginatedList(
            callback: fn($q) => $q->where('user_id', $userId)->latest(),
            relations: $this->relations,
        );
    }

    public function adminPaginated(?string $status, ?string $type, ?int $limit = null)
    {
        return $this->paginatedList(
            callback: function ($q) use ($status, $type) {
                if ($status) $q->where('status', $status);
                if ($type)   $q->where('listing_type', $type);
                $q->latest();
            },
            relations: $this->relations,
            limit: $limit,
        );
    }

    public function incrementViews(int $id): void
    {
        $this->model->newQuery()->where('id', $id)->increment('views_count');
    }
}
