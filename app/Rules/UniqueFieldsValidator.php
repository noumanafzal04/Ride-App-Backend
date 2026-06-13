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

        foreach ($fields as $field => $value) {

            if (blank($value)) {
                continue;
            }

            $query = $model::query()
                ->where($field, $value);

            foreach ($conditions as $col => $val) {
                $query->where($col, $val);
            }

            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }

            if ($query->exists()) {
                $validator->errors()->add(
                    $field,
                    ucfirst(str_replace('_', ' ', $field))
                    . ' already exists.'
                );
            }
        }
    }
}