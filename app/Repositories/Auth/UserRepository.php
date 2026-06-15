<?php
// app/Repositories/Auth/UserRepository.php

namespace App\Repositories\Auth;

use App\Models\User;
use App\Repositories\BaseRepository;

class UserRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new User();
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function verifyEmail(User $user): bool
    {
        return $user->update(['email_verified_at' => now()]);
    }
}
