<?php

namespace App\Traits;

trait RequestFiltering
{
    private function explodeToArray($value, $toLower = false): ?array
    {
        if (empty($value)) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $items = array_filter(
            array_map('trim', explode(',', $value)),
            fn($v) => $v !== ''
        );

        // Optional lowercase
        if ($toLower) {
            $items = array_map('strtolower', $items);
        }

        // Reindex the array
        return array_values($items);
    }

    private function explodeToArrayInt($value): ?array
    {
        if (empty($value)) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        return array_filter(
            array_map('intval', explode(',', $value))
        );
    }
}