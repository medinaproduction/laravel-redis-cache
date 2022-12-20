<?php

return [
    'enable-redis-cache' => [
        env('REDIS_CACHE_ENABLED', true),
    ],
    'cache-stores-hash' => [
        'driver' => 'redishash',
        'connection' => 'hash',
    ],
    'database-redis-hash' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_CRITICAL_DB', '2'),
    ]
];
