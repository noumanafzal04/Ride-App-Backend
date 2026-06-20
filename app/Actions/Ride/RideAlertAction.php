<?php

namespace App\Actions\Ride;

use App\Actions\BaseAction\BaseAction;
use App\Exceptions\ApiException;
use App\Repositories\Ride\RideAlertRepository;

class RideAlertAction extends BaseAction
{
    public function __construct(RideAlertRepository $repository)
    {
        parent::__construct($repository, 'ride_alert');
    }

    public function listForUser(int $userId)
    {
        return $this->repository->list(
            callback: fn($q) => $q->where('user_id', $userId)->where('is_active', true)->latest(),
            relations: ['fromCity:id,name', 'toCity:id,name'],
        );
    }

    /**
     * Create (or re-activate) an alert for the rider's route + optional date.
     * Idempotent — the same route/date toggled on twice reuses one row.
     */
    public function createForUser(int $userId, array $data)
    {
        $existing = $this->repository->findOne(
            callback: function ($q) use ($userId, $data) {
                $q->where('user_id', $userId)
                    ->where('from_city_id', $data['from_city_id'])
                    ->where('to_city_id', $data['to_city_id']);
                if (!empty($data['alert_date'])) {
                    $q->whereDate('alert_date', $data['alert_date']);
                } else {
                    $q->whereNull('alert_date');
                }
            }
        );

        if ($existing) {
            $this->repository->update($existing->id, ['is_active' => true]);
            return $this->repository->findOrFail($existing->id);
        }

        return $this->repository->create([
            'user_id'      => $userId,
            'from_city_id' => $data['from_city_id'],
            'to_city_id'   => $data['to_city_id'],
            'alert_date'   => $data['alert_date'] ?? null,
            'is_active'    => true,
        ]);
    }

    public function deleteForUser(int $userId, int $id): void
    {
        $alert = $this->repository->findOrFail($id);

        if ($alert->user_id !== $userId) {
            throw new ApiException('You do not own this alert.', 403);
        }

        $this->repository->deleteById($id);
    }
}
