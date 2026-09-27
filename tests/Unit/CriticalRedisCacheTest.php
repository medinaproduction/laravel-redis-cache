<?php

namespace MedinaProduction\RedisCache\Tests\Unit;

use Illuminate\Support\Facades\Log;
use MedinaProduction\RedisCache\Tests\Fixtures\SettingsCache;
use MedinaProduction\RedisCache\Tests\TestCase;

class CriticalRedisCacheTest extends TestCase
{
    public function test_it_gets_a_value(): void
    {
        $cache = new SettingsCache();

        $cache->put('theme', 'dark');

        $this->assertSame('dark', $cache->get('theme'));
    }

    public function test_it_logs_an_error_for_a_missing_key(): void
    {
        Log::shouldReceive('error')
            ->once()
            ->with('Critical cache [SettingsCache] with key [theme] has not been built.');

        $this->assertNull((new SettingsCache())->get('theme'));
    }
}
