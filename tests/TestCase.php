<?php

namespace MedinaProduction\RedisCache\Tests;

use Illuminate\Support\Facades\Redis;
use MedinaProduction\RedisCache\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Tests run against a real Redis server (REDIS_HOST, default 127.0.0.1) in
 * database 15. Every test uses its own cache prefix and only deletes its own
 * keys, so other data in the database is left alone.
 */
abstract class TestCase extends Orchestra
{
    protected string $cachePrefix;

    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $this->cachePrefix = 'redis-cache-tests-' . uniqid();

        $app['config']->set('cache.default', 'redis');
        $app['config']->set('cache.prefix', $this->cachePrefix);
        $app['config']->set('database.redis.client', 'phpredis');
        $app['config']->set('database.redis.options.prefix', '');
        $app['config']->set('redis-cache.database-redis-hash', [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => 15,
        ]);
    }

    protected function tearDown(): void
    {
        $connection = Redis::connection('hash');

        foreach ($connection->keys($this->cachePrefix . '*') as $key) {
            $connection->del($key);
        }

        parent::tearDown();
    }
}
