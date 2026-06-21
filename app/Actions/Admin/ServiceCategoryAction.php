<?php

namespace App\Actions\Admin;

use App\Exceptions\ApiException;
use App\Models\ServiceCategory;
use App\Repositories\Admin\ServiceCategoryRepository;
use Illuminate\Support\Str;

class ServiceCategoryAction
{
    public function __construct(protected ServiceCategoryRepository $repository) {}

    public function list()
    {
        return $this->repository->allForAdmin();
    }

    public function create(array $data): ServiceCategory
    {
        return $this->repository->create([
            'name'      => $data['name'],
            'slug'      => $this->uniqueSlug($data['name']),
            'icon'      => $data['icon'] ?? null,
            'sort'      => $data['sort'] ?? $this->repository->nextSort(),
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function update(int $id, array $data): ServiceCategory
    {
        $this->repository->findOrFail($id);

        $update = [];
        foreach (['name', 'icon', 'sort', 'is_active'] as $f) {
            if (array_key_exists($f, $data)) $update[$f] = $data[$f];
        }
        if (!empty($update)) $this->repository->update($id, $update);

        return $this->repository->findOrFail($id);
    }

    public function destroy(int $id): void
    {
        $category = $this->repository->findOrFail($id);

        if ($category->providers()->count() > 0) {
            throw new ApiException('This category is used by providers. Deactivate it instead of deleting.', 422);
        }

        $this->repository->deleteById($id);
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $i = 2;
        while ($this->repository->slugExists($slug)) {
            $slug = "$base-$i";
            $i++;
        }
        return $slug;
    }
}
