<?php 

namespace App\Traits\Helper;

use App\Exceptions\ApiException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;
use Closure;


trait DBHelper
{
    // validate associative array set
    private function validateArrayFieldExists(
        Builder|string|Model $modelClass,
        array $items,
        string $field,    
        string $errorKey = 'items',
        array $where = [],
        // by default it checks existing ids only within ids sent in request, 
        // set it to false when you need to check existing ids other than user sent in request in order to remove/unlink those ids from resource
        // so basically when set to false it will send missing ids to remove
        bool $applyWhereIn = true, 
    ): array|bool {
        
        $baseQuery = match (true) {
            $modelClass instanceof Builder => $modelClass,
            $modelClass instanceof Model   => $modelClass->newQuery(),
            is_string($modelClass)         => $modelClass::query(),
            default => throw new \InvalidArgumentException(__('global.invalid_model')),
        };

        $values = collect($items)
            ->pluck($field)
            ->filter()
            ->unique()
            ->values();

        if ($values->isEmpty()) {
            return false;
        }

        if($applyWhereIn)
            $query = $baseQuery->whereIn($field, $values);
        else
            $query = $baseQuery;

        foreach ($where as $column => $value) {
            $query->where($column, $value);
        }

        $existingIds = $query->pluck($field)->all();

        $invalidIds = array_diff($values->all(), $existingIds);

        if(!empty($invalidIds)){                                   
            throw ValidationException::withMessages([
                $errorKey => [ __('global.invalid_ids', ['ids' => implode(',', $invalidIds)])]
            ]);
        }

        return ['existingIds' => $existingIds, 'ids' => $values->all()];
    }

    // validate index array set like [1,2,4,5,2]
    private function validateArraySetExists(
        Builder|string|Model $modelClass,
        array $items,
        string $field,    
        string $errorKey = 'items',
        array $where = []
    ): void {
        
        $baseQuery = match (true) {
            $modelClass instanceof Builder => $modelClass,
            $modelClass instanceof Model   => $modelClass->newQuery(),
            is_string($modelClass)         => $modelClass::query(),
            default => throw new \InvalidArgumentException(__('global.invalid_model')),
        };        

        $query = $baseQuery->whereIn($field, $items);

        foreach ($where as $column => $value) {
            $query->where($column, $value);
        }

        $existingitemIds = $query->pluck($field)->all();
        $invalidIds = array_diff($items, $existingitemIds);

        if(!empty($invalidIds)){                                   
            throw ValidationException::withMessages([
                $errorKey => [ __('global.invalid_ids', ['ids' => implode(',', $invalidIds)])]
            ]);
        }
    }

    // used when there is need to check if two fields in a table linked to each other or not
    public function validateFieldPairsExist(
        string $arrayKey,
        string $model,
        string $fieldA,
        string $fieldB,
        string $dbFieldA,
        string $dbFieldB,
        array $select = ['*'],
        string $errorMessage = 'Invalid relationship between selected values.',
        ?callable $queryCallback = null,
    ): void {

        $items = collect($this->input($arrayKey, []));

        $pairs = $items
            ->map(function ($item, $index) use ($fieldA, $fieldB) {
                return [
                    'index' => $index,
                    'a' => $item[$fieldA] ?? null,
                    'b' => $item[$fieldB] ?? null,
                ];
            })
            ->filter(fn ($p) => $p['a'] !== null && $p['b'] !== null);

        if ($pairs->isEmpty()) {
            return;
        }

        // Extract unique values once (optimized)
        $aValues = $pairs->pluck('a')->unique()->values()->toArray();
        $bValues = $pairs->pluck('b')->unique()->values()->toArray();

        $query = $model::query()->select($select);

        // Optional external filtering (tenant/company/etc.)
        if ($queryCallback) {
            $queryCallback($query);
        }

        // Build pair-based validation query
        $query->where(function ($q) use ($pairs, $dbFieldA, $dbFieldB, $bValues) {

            $q->whereIn($dbFieldA, $pairs->pluck('a')->unique());

            $q->whereIn($dbFieldB, $bValues);
        });

        // Fetch valid pairs from DB
        $valid = $query->get()
            ->map(fn ($row) => $row->$dbFieldA . ':' . $row->$dbFieldB)
            ->flip();

        $errors = [];

        foreach ($pairs as $pair) {

            $key = $pair['a'] . ':' . $pair['b'];

            if (!isset($valid[$key])) {
                $errors["{$arrayKey}.{$pair['index']}.{$fieldB}"] = $errorMessage;
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    // show error if records already exists
    public function ensureNotExists(
        string $model,                
        ?string $message = null,
        ?Closure $callback = null
    ): void {

        $query = $model::query();

        if ($callback) {
            $callback($query);
        }

        if (! $query->exists()) {
            return;
        }

        $errorMessage =
            $message
            ?? __('global.record_already_exists');

        throw new ApiException(
            $errorMessage,
            422
        );
    }    

    protected function validateArrayFieldCombinationExists(
        string $parentField,
        string $modelClass,
        array $fields,
        ?string $message = null,
        ?int $ignoreId = null,
        callable|array|null $conditions = null
    ): void {

        // Skip if parent array not present
        if (! $this->has($parentField)) {
            return;
        }

        foreach ($this->input($parentField, []) as $index => $item) {

            $query = $modelClass::query();

            // Apply field combinations
            foreach ($fields as $field) {

                if (
                    ! array_key_exists($field, $item)
                    || blank($item[$field])
                ) {
                    continue 2; // skip current object
                }

                $query->where(
                    $field,
                    $item[$field]
                );
            }

            // Extra conditions
            if ($conditions !== null) {

                if (is_callable($conditions)) {

                    $conditions(
                        $query,
                        $item,
                        $index
                    );

                } elseif (is_array($conditions)) {

                    foreach (
                        $conditions
                        as $column => $value
                    ) {

                        $query->where(
                            $column,
                            $value
                        );
                    }
                }
            }

            // Ignore record (update)
            if ($ignoreId) {

                $query->where(
                    'id',
                    '!=',
                    $ignoreId
                );
            }

            if ($query->exists()) {
                throw ValidationException::withMessages([
                    "{$parentField}.{$index}.{$fields[0]}" => $message ?? implode(', ', $fields).' combination already exists.'
                ]);
            }
        }
    }
}