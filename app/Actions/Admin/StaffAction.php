<?php

namespace App\Actions\Admin;

use App\Exceptions\ApiException;
use App\Models\AdminUser;
use App\Repositories\Admin\AdminUserRepository;
use Illuminate\Support\Facades\DB;

class StaffAction
{
    public function __construct(protected AdminUserRepository $repository) {}

    public function list()
    {
        return $this->repository->paginatedList2();
    }

    public function create(array $data): AdminUser
    {
        return DB::transaction(function () use ($data) {
            if ($this->repository->emailExists($data['email'])) {
                throw new ApiException('A staff member with this email already exists.', 422);
            }

            $admin = $this->repository->create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => $data['password'], // hashed via cast
                'role_id'  => $data['role_id'] ?? null,
                'status'   => $data['status'] ?? 'active',
            ]);

            return $admin->load('role:id,name,slug');
        });
    }

    public function update(int $id, array $data): AdminUser
    {
        return DB::transaction(function () use ($id, $data) {
            $admin = $this->repository->findOrFail($id);

            if (!empty($data['email']) && $this->repository->emailExists($data['email'], $id)) {
                throw new ApiException('A staff member with this email already exists.', 422);
            }

            $update = [];
            foreach (['name', 'email', 'role_id', 'status'] as $f) {
                if (array_key_exists($f, $data)) $update[$f] = $data[$f];
            }
            if (!empty($data['password'])) $update['password'] = $data['password'];

            if (!empty($update)) $this->repository->update($id, $update);

            return $this->repository->findOrFail($id)->load('role:id,name,slug');
        });
    }

    public function destroy(int $id, int $actingAdminId): void
    {
        if ($id === $actingAdminId) {
            throw new ApiException('You cannot delete your own account.', 422);
        }
        $this->repository->findOrFail($id);
        $this->repository->deleteById($id);
    }
}
