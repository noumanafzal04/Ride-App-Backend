<?php

namespace App\Actions\BaseAction;

use App\Exceptions\ApiException;
use Illuminate\Support\Facades\DB;

class BaseAction
{
    protected $repository;
    protected string $resourceName;
    protected array $childDataKeys = [];
    protected array $beforeUpdateData = [];

    // Constructor accepts repository and resource name
    public function __construct($repository, string $resourceName)
    {
        $this->repository = $repository;
        $this->resourceName = $resourceName;
    }

    public function all(int $companyId, ?array $filters = [])
    {
        return $this->repository->paginate();
    }

    public function show($companyId, $id)
    {
        return $this->repository->show($id);
    }

    public function create(int $companyId, $data)
    {
        return DB::transaction(function () use ($data, $companyId) {

            $data = $this->beforeCreate($companyId, $data);

            // Extract child data if any
            $childData = $this->extractChildData($data);

            $created = $this->repository->create($data);
            if (!$created) {
                throw new ApiException(__("{$this->resourceName}.create_failed"), 422);
            }

            $this->afterCreate($created, $companyId, $childData);

            return $created;
        });
    }

    public function insert(int $companyId, $data)
    {
        return DB::transaction(function () use ($data, $companyId) {

            $data = $this->prepareInsertData($companyId, $data);

            $inserted = $this->repository->insert($data);
            if (!$inserted) {
                throw new ApiException(__("{$this->resourceName}.insert_failed"), 422);
            }

            $this->afterInsert($companyId, $data);

            return $inserted;
        });
    }

    public function update(int $companyId, $id, $data, $options = [])
    {
        return DB::transaction(function () use ($data, $companyId, $id, $options) {

            if (empty($options['bypassBeforeHock']))
                $data = $this->beforeUpdate($companyId, $id, $data);

            // Extract child data if any
            $childData = $this->extractChildData($data);

            $updated = !empty($options['byModel'])
                ? $this->repository->updateByModel($id, $data)
                : $this->repository->update($id, $data);

            if (!$updated) {
                throw new ApiException(__("{$this->resourceName}.update_failed"), 422);
            }

            $this->afterUpdate($updated, $companyId, $id, $childData);

            return $updated;
        });
    }

    public function destroy($companyId, $id, $options = [])
    {
        return DB::transaction(function () use ($companyId, $id, $options) {

            $this->beforeDestroy($companyId, $id);

            $isDelete = $this->repository->deleteById($id, $options);
            if (!$isDelete) {
                throw new ApiException(__("{$this->resourceName}.delete_failed"), 422);
            }

            $this->afterDestroy($companyId, $id);

            return $isDelete;
        });
    }

    public function setDefault(int $companyId, $id, $data)
    {
        return DB::transaction(function () use ($companyId, $id, $data) {

            $reset = $this->repository->updateByConditions([], ['is_default' => false]);

            $updated = $this->repository->updateByConditions(['id' => $id], ['usps_post_net' => $data['usps_post_net'], 'is_default' => true]);

            if (!$reset || !$updated) {
                throw new ApiException(__("{$this->resourceName}.default_failed"), 422);
            }
        });
    }

    /**
     * Hooks
     */

    protected function prepareInsertData(int $companyId, $data)
    {
        return $data;
    }

    protected function afterInsert(int $companyId, $data): void
    {
        // Override in child class if needed
    }

    protected function beforeCreate(int $companyId, $data)
    {
        // Override in child class if needed
        return $data;
    }

    protected function afterCreate($created, int $companyId, $data): void
    {
        // Override in child class if needed
    }

    protected function beforeUpdate(int $companyId, $id, $data)
    {
        // Override in child class if needed
        return $data;
    }


    protected function afterUpdate($updated, int $companyId, $id, $data): void
    {
        // Override in child class if needed
    }

    protected function beforeDestroy(int $companyId, $id): void
    {
        // Override in child class if needed
    }

    protected function afterDestroy(int $companyId, $id): void
    {
        // Override in child class if needed
    }

    protected function extractChildData(array &$data): array
    {
        $childData = [];

        foreach ($this->childDataKeys as $key) {
            if (isset($data[$key])) {
                $childData[$key] = $data[$key];
                unset($data[$key]);
            }
        }

        return $childData;
    }

    public function toggleStatus(int $companyId, $id): void
    {
        DB::transaction(function () use ($id) {

            $record = $this->repository->findOrFail($id);

            $record->update([
                'status' => $record->status->toggle(),
            ]);
        });
    }
}
