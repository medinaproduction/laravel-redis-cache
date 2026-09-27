<?php

namespace MedinaProduction\RedisCache\Tests\Fixtures;

use MedinaProduction\RedisCache\BaseRedisCache;

class MerchantCache extends BaseRedisCache
{
    protected $location = 'merchant';
}
