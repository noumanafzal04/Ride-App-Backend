<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class GenericRepository
{
    /** @var Model */
    protected $model;

    /** @var Builder */
    protected $query;

    public function __construct()
    {
        $this->query = $this->model->newQuery();
    }

    /**
     * Reset the query builder
     */
    public function newQuery(): self
    {
        $this->query = $this->model->newQuery();
        return $this;
    }

    /**
     * Fluent where conditions
     */
    public function where(array|callable $conditions): self
    {
        if (is_callable($conditions)) {
            $conditions($this->query);
        } else {
            $this->query->where($conditions);
        }
        return $this;
    }

    /**
     * WhereIn condition
     */
    public function whereIn(string $field, array $values): self
    {
        $this->query->whereIn($field, $values);
        return $this;
    }

    /**
     * WhereNotIn condition
     */
    public function whereNotIn(string $field, array $values): self
    {
        $this->query->whereNotIn($field, $values);
        return $this;
    }

    /**
     * Eager load relations
     */
    public function with(array|string $relations): self
    {
        $this->query->with($relations);
        return $this;
    }

    /**
     * Order by
     */
    public function orderBy(string $field, string $direction = 'asc'): self
    {
        $this->query->orderBy($field, $direction);
        return $this;
    }

    /**
     * Limit
     */
    public function limit(int $count): self
    {
        $this->query->limit($count);
        return $this;
    }

    /**
     * Offset
     */
    public function offset(int $offset): self
    {
        $this->query->offset($offset);
        return $this;
    }

    /**
     * Get results
     */
    public function get(array $columns = ['*'])
    {
        return $this->query->get($columns);
    }

    /**
     * First result
     */
    public function first(array $columns = ['*'])
    {
        return $this->query->first($columns);
    }

    /**
     * Paginate results
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->query->paginate($perPage, $columns);
    }

    /**
     * Pluck a field
     */
    public function pluck(string $field, ?string $key = null)
    {
        return $this->query->pluck($field, $key);
    }

    /**
     * Count
     */
    public function count(): int
    {
        return $this->query->count();
    }

    /**
     * Delete
     */
    public function delete(): int
    {
        return $this->query->delete();
    }

    /**
     * Create a new record
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Insert multiple rows
     */
    public function insert(array $data): bool
    {
        return $this->model->insert($data);
    }

    /**
     * Update records
     */
    public function update(array $data): int
    {
        return $this->query->update($data);
    }

    /**
     * Upsert records
     */
    public function upsert(array $rows, array $uniqueBy, array $updateColumns)
    {
        return $this->model->upsert($rows, $uniqueBy, $updateColumns);
    }
}
