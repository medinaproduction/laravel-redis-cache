<?php

namespace MedinaProduction\RedisCache;

use Illuminate\Support\Facades\Cache;
use MedinaProduction\RedisCache\Extensions\RedisHashStore;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/redis-cache.php', 'redis-cache'
        );
    }

    public function boot(): void
    {
        config(['cache.stores.hash' => config('redis-cache.cache-stores-hash')]);
        config(['database.redis.hash' => config('redis-cache.database-redis-hash')]);

        Cache::extend('redishash', function ($app) {
            $redis = $app['redis'];
            $connection = config('redis-cache.cache-stores-hash.connection', 'default');
            $prefix = $app['config']['cache']['prefix'];

            return Cache::repository(new RedisHashStore($redis, $prefix, $connection));
        });

        $this->publishes([
            __DIR__ . '/../config/redis-cache.php' => config_path('redis-cache.php'),
        ]);
    }
}
