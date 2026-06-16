<?php

namespace App\Actions;

use App\Actions\BaseAction\BaseAction;
use App\Constants\ResourceFields;
use App\Repositories\WorldRepository;

class WorldAction extends BaseAction
{
    public function __construct(
        WorldRepository $repository,
    ) {
        parent::__construct($repository, 'cities');
    }

    public function list($filters)
    {
        return $this->repository->list(
            callback: function ($query) use ($filters) {

                // keyword search (simple)
                if (!empty($filters['keywords'])) {
                    $search = $filters['keywords'];
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
                }
            },

            select: ResourceFields::CITIES_LIST_FIELDS
        );
    }
}
