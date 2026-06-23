<?php

namespace App\Repositories\Rental;

use App\Models\RentalCar;
use App\Repositories\BaseRepository;

class RentalCarRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new RentalCar();
    }

    protected array $relations = [
        'images', 'city:id,name', 'owner:id,first_name,last_name,phone_number',
        'inspectionRequest:id,status,overall_grade,overall_score',
    ];

    public function paginatedBrowse(array $filters, ?float $nearLat = null, ?float $nearLng = null)
    {
        return $this->paginatedList(
            callback: function ($q) use ($filters, $nearLat, $nearLng) {
                $q->where('rental_cars.status', RentalCar::STATUS_ACTIVE);

                if (!empty($filters['q'])) {
                    $s = $filters['q'];
                    $q->where(fn($w) => $w->where('make', 'like', "%{$s}%")->orWhere('model', 'like', "%{$s}%"));
                }
                if (!empty($filters['category']))    $q->where('category', $filters['category']);
                if (!empty($filters['city_id']))     $q->where('rental_cars.city_id', $filters['city_id']);
                if (!empty($filters['rental_type'])) {
                    $rt = $filters['rental_type'];
                    $q->where(fn($w) => $w->where('rental_type', $rt)->orWhere('rental_type', 'both'));
                }
                if (!empty($filters['transmission'])) $q->where('transmission', $filters['transmission']);
                if (!empty($filters['price_max']))    $q->where('price_per_day', '<=', $filters['price_max']);

                $sort = $filters['sort'] ?? null;
                if ($sort === 'price_asc')      { $q->orderBy('price_per_day'); }
                elseif ($sort === 'price_desc') { $q->orderByDesc('price_per_day'); }
                elseif ($nearLat !== null && $nearLng !== null && empty($filters['city_id'])) {
                    $q->leftJoin('cities', 'cities.id', '=', 'rental_cars.city_id')
                        ->select('rental_cars.*')
                        ->selectRaw(
                            '( 6371 * acos( least(1, greatest(-1,'
                            . ' cos(radians(?)) * cos(radians(cities.lat)) * cos(radians(cities.lon) - radians(?))'
                            . ' + sin(radians(?)) * sin(radians(cities.lat)) ))) ) AS distance_km',
                            [$nearLat, $nearLng, $nearLat]
                        )
                        ->orderByRaw('distance_km IS NULL')
                        ->orderBy('distance_km')
                        ->orderByDesc('rental_cars.is_featured');
                } else {
                    $q->orderByDesc('is_featured')->latest('rental_cars.created_at');
                }
            },
            relations: $this->relations,
        );
    }

    public function findActiveWithRelations(int $id): ?RentalCar
    {
        return $this->findOne(
            callback: fn($q) => $q->where('id', $id)->where('status', RentalCar::STATUS_ACTIVE),
            relations: $this->relations,
        );
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
