<?php

namespace MedinaProduction\RedisCache;

use MedinaProduction\RedisCache\Extensions\RedisHashStore;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * @property CacheRepository cache
 * @property string base_location
 * @property string location
 * @property string namespace
 * @property boolean enabled
 */
abstract class BaseRedisCache
{
    protected CacheRepository $cache;
    protected string $base_location = 'general';
    protected bool $enabled = true;
    protected bool $serialize = true;
    protected string $namespace;

    public function __construct()
    {
        $this->namespace = $this->getPrefix();

        if (config('redis-cache.enable-redis-cache') === true) {
            $this->setEnabled(true);
        }

        if (request()->has('clear-cache')) {
            $this->setEnabled(false);
        }

        $this->cache = $this->resolveCacheStore();
    }

    public function put(string $key, $value, $ttl = null): BaseRedisCache
    {
        if ($this->supportsNamespaces()) {
            $store = $this->cache->namespace($this->namespace);
        } else {
            $store = $this->cache;
        }

        if ($ttl) {
            dump('adding for ' . $ttl . ' seconds');

            $store->add($key, $value, $ttl);
        } else {
            $store->forever($key, $value);
        }

        return $this;
    }

    public function get(string $key): mixed
    {
        if ($this->isEnabled()) {
            if ($this->supportsNamespaces()) {
                return $this->cache->namespace($this->namespace)->get($key);
            } else {
                return $this->cache->get($key);
            }
        }

        return null;
    }

    public function getAll(): Collection
    {
        $path = config('cache.prefix') . ':' . $this->getPrefix();

        $all = $this->cache->connection()->hgetall($path);

        if ($this->serialize) {
            foreach ($all as $key => $value) {
                $all[$key] = unserialize($value);
            }
        }

        return collect($all);
    }

    /**
     * Delete key in cache.
     */
    public function delete(string $key)
    {
        if ($this->supportsNamespaces()) {
            $this->cache->namespace($this->namespace)->forget($key);
        } else {
            $this->cache->forget($key);
        }

        return $this;
    }

    /**
     * Store multiple values in cache.
     *
     * @todo Test
     *
     * @param array $values
     *
     * @return $this;
     */
    public function setMultiples(array $values)
    {
        if ($this->supportsNamespaces()) {
            $this->cache->namespace($this->namespace)->putMany($values);
        } else {
            $this->cache->putMany($values);
        }

        return $this;
    }

    /**
     * Get the base and location key combined.
     *
     * @return string
     */
    protected function getPrefix()
    {
        return $this->base_location . ':' . $this->location;
    }

    /**
     * Clear entire cache for current location.
     *
     * @return integer
     */
    public function clear(): int
    {
        if ($this->supportsNamespaces()) {
            return $this->cache->namespace($this->namespace)->flush();
        } else {
            return $this->cache->flush();
        }
    }

    /**
     * Is the cache enabled or not?
     *
     * @return boolean
     */
    protected function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Turn the cache on or off
     *
     * @param boolean $isEnabled
     *
     * @return BaseRedisCache
     */
    protected function setEnabled(bool $isEnabled = true): BaseRedisCache
    {
        $this->enabled = $isEnabled;

        return $this;
    }

    /**
     * Resolve and return the Cache Repository we need to use
     *
     * @return CacheRepository
     */
    protected function resolveCacheStore(): CacheRepository
    {
        return Cache::store(config('cache.default') == 'redis' ? 'hash' : '');
    }

    /**
     * Do we support namespaces?
     *
     * @return boolean
     */
    private function supportsNamespaces(): bool
    {
        return get_class($this->cache->getStore()) === RedisHashStore::class;
    }
}
