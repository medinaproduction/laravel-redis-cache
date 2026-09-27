<?php

namespace MedinaProduction\RedisCache\Tests\Fixtures;

use MedinaProduction\RedisCache\CriticalRedisCache;

class SettingsCache extends CriticalRedisCache
{
    protected $location = 'settings';
}
