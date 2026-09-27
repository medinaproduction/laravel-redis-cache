<?php

namespace MedinaProduction\RedisCache\Tests\Unit;

use Illuminate\Support\Facades\Redis;
use MedinaProduction\RedisCache\Tests\Fixtures\MerchantCache;
use MedinaProduction\RedisCache\Tests\Fixtures\PageViewCache;
use MedinaProduction\RedisCache\Tests\TestCase;

class BaseRedisCacheTest extends TestCase
{
    public function test_it_puts_and_gets_a_value(): void
    {
        $cache = new MerchantCache();

        $cache->put('bee-shop', ['uuid' => 'abc', 'site' => 'https://bee.shop']);

        $this->assertSame(['uuid' => 'abc', 'site' => 'https://bee.shop'], $cache->get('bee-shop'));
    }

    public function test_it_returns_null_for_a_missing_key(): void
    {
        $this->assertNull((new MerchantCache())->get('missing'));
    }

    public function test_it_stores_values_in_one_hash_per_location(): void
    {
        (new MerchantCache())->put('bee-shop', ['uuid' => 'abc']);

        $this->assertSame(
            ['bee-shop'],
            Redis::connection('hash')->hkeys($this->cachePrefix . ':general:merchant')
        );
    }

    public function test_it_gets_all_values_for_the_location(): void
    {
        $cache = new MerchantCache();

        $cache->put('bee-shop', ['uuid' => 'abc']);
        $cache->put('honey-shop', ['uuid' => 'def']);
        (new PageViewCache())->put('view', ['url' => 'https://bee.shop']);

        $this->assertEquals([
            'bee-shop' => ['uuid' => 'abc'],
            'honey-shop' => ['uuid' => 'def'],
        ], $cache->getAll()->sortKeys()->all());
    }

    public function test_it_deletes_a_value(): void
    {
        $cache = new MerchantCache();

        $cache->put('bee-shop', ['uuid' => 'abc']);
        $cache->put('honey-shop', ['uuid' => 'def']);
        $cache->delete('bee-shop');

        $this->assertNull($cache->get('bee-shop'));
        $this->assertSame(['uuid' => 'def'], $cache->get('honey-shop'));
    }

    public function test_it_clears_only_its_own_location(): void
    {
        $merchants = new MerchantCache();
        $pageViews = new PageViewCache();

        $merchants->put('bee-shop', ['uuid' => 'abc']);
        $pageViews->put('view', ['url' => 'https://bee.shop']);

        $merchants->clear();

        $this->assertTrue($merchants->getAll()->isEmpty());
        $this->assertSame(['url' => 'https://bee.shop'], $pageViews->get('view'));
    }
}
