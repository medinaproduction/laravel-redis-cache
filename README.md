# Laravel Redis Cache

```
composer require medinaproduction/laravel-redis-cache
```

## Requirements

| Version | Laravel | PHP |
|---|---|---|
| 1.x | 13 | 8.3, 8.4, 8.5 |
| 0.1.x | 8 to 10 | 8.1 or later |

## Tests

The tests need a Redis server (`REDIS_HOST`, default `127.0.0.1`) and the phpredis extension. They use database 15 and only delete their own keys.

```
composer install
vendor/bin/phpunit
```
