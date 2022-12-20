<?php

namespace MedinaProduction\RedisCache;

use Illuminate\Support\Facades\Log;

abstract class CriticalRedisCache extends BaseRedisCache
{
    protected string $base_location = 'critical';

    /**
     * Get value from cache.
     */
    public function get(string $key): mixed
    {
        $value = parent::get($key);
        if ($value === null) {
            $message = 'Critical cache [' . class_basename($this) . '] with key [' . $key . '] has not been built.';
            Log::error($message);
        }

        return $value;
    }

    /**
     * Critical cache is always enabled.
     *
     * @return boolean
     */
    protected function isEnabled(): bool
    {
        return true;
    }
}
