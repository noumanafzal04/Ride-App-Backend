<?php

namespace App\Support;

use InvalidArgumentException;

class BuildsWithRelations
{
    public static function relations(
        array $definitions,
        ?array $onlyRelations = null,
        array $overrides = []
    ): array {
        // filter only requested relations (if provided)
        $relations = $onlyRelations
            ? array_intersect_key($definitions, array_flip($onlyRelations))
            : $definitions;

        return collect($relations)
            ->map(function ($config, $relation) use ($overrides) {

                // apply override if exists
                if (isset($overrides[$relation])) {
                    $config = array_merge($config, $overrides[$relation]);
                }

                // resolve select fields
                if (!empty($config['select'])) {
                    return $relation . ':' . implode(',', $config['select']);
                }

                return $relation;
            })
            ->values()
            ->toArray();
    }
}