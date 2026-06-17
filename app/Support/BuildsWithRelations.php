<?php

namespace App\Support;

class BuildsWithRelations
{
    public static function relations(
        array $definitions,
        ?array $onlyRelations = null,
        array $overrides = []
    ): array {

        $relations = $onlyRelations
            ? array_intersect_key($definitions, array_flip($onlyRelations))
            : $definitions;

        $result = [];

        foreach ($relations as $relation => $config) {

            if (isset($overrides[$relation])) {
                $config = array_merge($config, $overrides[$relation]);
            }

            $select = $config['select'] ?? [];

            if (str_contains($relation, '.')) {
                // nested — MUST use closure, colon syntax doesn't work
                if (!empty($select)) {
                    $fields = $select;
                    $result[$relation] = fn($query) => $query->select($fields);
                } else {
                    $result[$relation] = fn($query) => $query;
                }
            } else {
                // top-level — colon syntax works
                if (!empty($select)) {
                    $result[] = $relation . ':' . implode(',', $select);
                } else {
                    $result[] = $relation;
                }
            }
        }

        return $result;
    }
}
