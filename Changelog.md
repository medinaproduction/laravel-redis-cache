# Laravel Redis Cache Changelog

## 1.0.0
#### 2026-09-27

- Requires Laravel 13 and PHP 8.3 or later. Tested on PHP 8.3, 8.4 and 8.5
- Dropped support for Laravel 8 to 10 and PHP 8.1 and 8.2. Stay on 0.1.x for those
- Added `RedisHashStore::touch()`, which Laravel 13's cache store contract requires. It returns `false`, because items in a hash can't expire one by one
- Missing keys now return `null` instead of `false` with phpredis, so `CriticalRedisCache` logs them and `Cache::remember()` treats them as missing
- Removed a leftover `dump()` from `BaseRedisCache::put()` with a TTL
- Tests are no longer autoloaded in projects that install the package
- Fixed the test setup and added tests that run against Redis

### Known issues

These were broken before 1.0.0 and are unchanged:

- `RedisHashStore::put()` with a TTL, `many()`, `decrement()` and `lock()` don't work
- `BaseRedisCache::setMultiples()` fails when the hash store is in use
