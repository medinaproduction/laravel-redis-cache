<?php

namespace MedinaProduction\RedisCache\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use MedinaProduction\RedisCache\Extensions\RedisHashStore;
use MedinaProduction\RedisCache\Tests\TestCase;

class RedisHashStoreTest extends TestCase
{
    public function test_the_hash_store_is_registered(): void
    {
        $this->assertInstanceOf(RedisHashStore::class, Cache::store('hash')->getStore());
    }

    public function test_it_stores_forever_gets_and_forgets(): void
    {
        $cache = Cache::store('hash');

        $this->assertTrue($cache->forever('bee', ['name' => 'Perdita']));
        $this->assertSame(['name' => 'Perdita'], $cache->get('bee'));

        $this->assertTrue($cache->forget('bee'));
        $this->assertNull($cache->get('bee'));
    }

    public function test_it_increments_numbers(): void
    {
        $cache = Cache::store('hash');

        $cache->forever('count', 1);

        $this->assertSame(3, $cache->increment('count', 2));
        $this->assertEquals(3, $cache->get('count'));
    }

    public function test_it_flushes_the_namespace(): void
    {
        $cache = Cache::store('hash');

        $cache->namespace('one')->forever('bee', 'a');
        $cache->namespace('two')->forever('bee', 'b');
        $cache->namespace('one')->flush();

        $this->assertNull($cache->namespace('one')->get('bee'));
        $this->assertSame('b', $cache->namespace('two')->get('bee'));
    }

    public function test_touch_does_not_set_an_expiration(): void
    {
        $cache = Cache::store('hash');

        $cache->forever('bee', 'a');

        $this->assertFalse($cache->touch('bee', 60));
        $this->assertSame('a', $cache->get('bee'));
    }
}
