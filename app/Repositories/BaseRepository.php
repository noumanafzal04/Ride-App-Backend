<?php

namespace App\Repositories;

use App\Traits\ResolvesPaginationLimit;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    use ResolvesPaginationLimit;

    protected $model;

    public function query()
    {
        return $this->model->newQuery();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function insert(array $data): bool
    {
        return $this->model->insert($data);
    }

    public function update($id, $data)
    {
        return $this->model->where('id', $id)->update($data);
    }

    public function updateByConditions($conditions, $data)
    {
        return $this->model->where($conditions)->update($data);
    }

    public function updateWithModel(Model $model, array $data): Model
    {
        $model->update($data);
        return $model;
    }

    public function updateByModel(int $id, $data): Model
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);
        return $record;
    }

    public function deleteById($id, array $options = [])
    {
        $q = $this->model->where('id', $id);

        if (!empty($options['conditions']))
            $q->where($options['conditions']);

        if (!empty($options['force']))
            return $q->forceDelete();
        else
            return $q->delete();
    }

    public function deleteWithConditions(array $conditions, array $options = []): int
    {
        $q = $this->model->where($conditions);
        if (!empty($options['force']))
            return $q->forceDelete();
        else
            return $q->delete();
    }

    public function deleteWhereIn(string $field, array $ids, array $options = []): int
    {
        $q = $this->model->whereIn($field, $ids);

        if (!empty($options['conditions']))
            $q->where($options['conditions']);

        if (!empty($options['force']))
            return $q->forceDelete();
        else
            return $q->delete();
    }

    public function pluckByConditions(array $conditions, string $field)
    {
        return $this->model->where($conditions)->pluck($field)->all();
    }

    public function elequentConditions(array $conditions)
    {
        return $this->model->where($conditions);
    }

    public function findOrFail($id, array $select = [], array $relations = [])
    {
        $q = $this->model->newQuery();

        if (!empty($select))
            $q->select($select);

        if (!empty($relations))
            $q->with($relations);

        return $q->findOrFail($id);
    }

    public function show($id, array $relations = [], array $select = [])
    {
        $query = $this->model->where('id', $id)->with($relations);

        if (!empty($select)) {
            $query->select($select);
        }

        return $query->firstOrFail();
    }

    public function paginate(?int $limit = null, ?array $relations = [])
    {
        $limit = $this->resolveLimit($limit);
        return $this->model->with($relations)->paginate($limit);
    }

    public function paginateWithFilters(?int $limit = null, array $relations, array $filters = [], array $select = [], array $conditions = [], ?callable $callback = null)
    {
        $limit = $this->resolveLimit($limit);

        $query = $this->model->with($relations);

        if (!empty($select))
            $query->select($select);

        if (!empty($conditions))
            $query->where($conditions);

        if (!empty($callback))
            $callback($query);

        $query = $this->applyFilters($query, $filters);

        return $query->paginate($limit);
    }

    public function get(array $select = [], array $conditions = [], array $relations = [], array $whereIn = [])
    {

        $query = $this->model->with($relations);

        if (!empty($select))
            $query->select($select);

        if (!empty($conditions))
            $query->where($conditions);

        // Expected format: [['column' => 'company_id', 'values' => [1, 2, 3]], ...]
        foreach ($whereIn as $clause) {
            if (!empty($clause['values'])) {
                $query->whereIn($clause['column'], $clause['values']);
            }
        }

        return $query->get();
    }

    // public function get($options = []){
    //     $q = $this->model->newQuery();

    //     if (!empty($options['select']))
    //         $q->select($options['select']);

    //     if (!empty($options['conditions']))
    //         $q->where($options['conditions']);

    //     if (!empty($options['relations']))
    //         $q->with($options['relations']);

    //     return $q->get();
    // }

    public function upsert(array $rows, $match, $update)
    {
        return $this->model->upsert(
            $rows,
            $match,
            $update
        );
    }

    public function updateOrCreate($conditions, $fields)
    {
        return $this->model->updateOrCreate($conditions, $fields);
    }

    public function list(?callable $callback = null, array $select = [], ?array $relations = [])
    {
        $query = $this->model->newQuery();

        // Apply custom conditions
        if ($callback) {
            $callback($query);
        }

        if (!empty($select)) {
            $query->select($select);
        }

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    public function paginatedList(?callable $callback = null, array $select = [], array $relations = [], ?int $limit = null)
    {
        $limit = $this->resolveLimit($limit);

        $query = $this->model->newQuery();

        if ($callback) {
            $callback($query);
        }

        if (!empty($select)) {
            $query->select($select);
        }

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->paginate($limit);
    }

    /**
     * Default no-op filter
     * Child repositories can override this
     */
    protected function applyFilters($query, array $filters)
    {
        return $query;
    }

    public function findOne(?callable $callback = null, array $select = [], array $relations = [])
    {
        $query = $this->model->newQuery();

        if ($callback) {
            $callback($query);
        }

        if (!empty($select)) {
            $query->select($select);
        }

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->first();
    }
}