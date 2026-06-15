<?php

namespace App\Rules;

class UniqueFieldsValidator
{
    public static function validate(
        $validator,
        string $model,
        array $fields,
        array $conditions = [],
        ?int $ignoreId = null
    ): void {

        foreach ($fields as $attribute => $value) {

            if (blank($value)) {
                continue;
            }

            $column = str($attribute)->afterLast('.')->toString();

            $query = $model::query()->where($column, $value);

            foreach ($conditions as $col => $val) {
                $query->where($col, $val);
            }

            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }

            if ($query->exists()) {
                $validator->errors()->add(
                    $attribute,
                    ucfirst(str_replace('_', ' ', $column)) . ' already exists.'
                );
            }
        }
    }
}
