<?php

namespace MedinaProduction\RedisCache\Tests\Fixtures;

use MedinaProduction\RedisCache\BaseRedisCache;

class PageViewCache extends BaseRedisCache
{
    protected $location = 'page-view';
}
