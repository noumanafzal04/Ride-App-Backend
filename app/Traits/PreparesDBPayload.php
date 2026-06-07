<?php

namespace App\Traits;

use App\Exceptions\ApiException;

trait PreparesDBPayload
{    
    private function preparePayload(array $payload, array $keysWithDefaults, array $extra = []): array
    {
        $prepared = [];

        foreach ($keysWithDefaults as $key => $default) {
            // If numeric keys provided, assume default null
            if (is_int($key)) {
                $key = $default;
                $default = null;
            }

            $prepared[$key] = $payload[$key] ?? $default;
        }

        // Append / override with extra values
        return array_merge($prepared, $extra);
    }

    private function appendToArrays(array $arrayOfArrays, array $keysWithValues): array
    {
        return array_map(function ($subArray) use ($keysWithValues) {
            foreach ($keysWithValues as $key => $value) {
                // Only add if not already present
                if (!array_key_exists($key, $subArray)) {
                    $subArray[$key] = $value;
                }
            }
            return $subArray;
        }, $arrayOfArrays);
    }

    private function removeKey(array $array, string $keyToRemove): array
    {
        if (array_key_exists($keyToRemove, $array)) {
            unset($array[$keyToRemove]);
        }
        return $array;
    }

    private function removeKeys(array $array, array $keysToRemove): array
    {
        foreach ($keysToRemove as $key) {
            if (array_key_exists($key, $array)) {
                unset($array[$key]);
            }
        }
        return $array;
    }

    function mapOnlyRequestToDb(array $data, array $map, bool $strict = true): array
    {
        $result = [];

        foreach ($map as $from => $to) {
            if (! array_key_exists($from, $data)) {
                if ($strict) {
                    throw new ApiException("Missing required field: {$from}");
                }
                continue;
            }

            $result[$to] = $data[$from];
        }

        return $result;
    }
}
