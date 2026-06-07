<?php

namespace App\Traits;

trait ResolvesPaginationLimit
{
    public function resolveLimit(?int $limit = null, string $param = 'limit'): int
    {
        $requestLimit = request()->input($param);

        // Priority: request > passed param > config
        $finalLimit = $requestLimit ?? $limit ?? config('pagination.default');

        $finalLimit = (int) $finalLimit;

        $max = config('pagination.max', 100);

        return min(max(1, $finalLimit), $max);
    }
}