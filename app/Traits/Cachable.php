<?php 

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait Cachable
{
    protected function cacheRemember(string $key, int $ttlSeconds, callable $callback)
    {
        return Cache::remember($key, $ttlSeconds, $callback);
    }

    protected function cacheForget(string $key)
    {
        Cache::forget($key);
    }
}