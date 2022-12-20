<?php

namespace MedinaProduction\RedisCache\Tests;

use Orchestra\RedisCache\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }
}
