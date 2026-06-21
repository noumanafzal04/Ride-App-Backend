<?php

namespace App\Repositories\Admin;

use App\Models\AdminUser;
use App\Repositories\BaseRepository;

class AdminUserRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new AdminUser();
    }

    public function paginatedList2(?int $limit = null)
    {
        return $this->list(
            callback: fn($q) => $q->latest(),
            relations: ['role:id,name,slug'],
        );
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        return $this->model->newQuery()
            ->where('email', $email)
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->exists();
    }
}
