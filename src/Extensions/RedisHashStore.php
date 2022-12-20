<?php

namespace MedinaProduction\RedisCache\Extensions;

use Illuminate\Contracts\Cache\Store;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Redis\Factory as Redis;

class RedisHashStore implements Store, LockProvider
{
    /**
     * The Redis factory implementation.
     *
     * @var \Illuminate\Contracts\Redis\Factory
     */
    protected $redis;

    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;

    /**
     * A string that should be suffixed to keys.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * The Redis connection that should be used.
     *
     * @var string
     */
    protected $connection;

    /**
     * Create a new Redis store.
     *
     * @param Redis $redis
     * @param string $prefix
     * @param string $connection
     * @return void
     */
    public function __construct(Redis $redis, string $prefix = '', string $connection = 'default')
    {
        $this->redis = $redis;
        $this->setPrefix($prefix);
        $this->setConnection($connection);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param string|array $key
     * @return mixed
     */
    public function get($key)
    {
        $value = $this->connection()->hget($this->prefix . $this->namespace, $key);

        return !is_null($value) ? $this->unserialize($value) : null;
    }

    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value.
     *
     * @param array $keys
     * @return array
     */
    public function many(array $keys)
    {
        $results = [];

        $values = $this->connection()->hmget(array_map(function ($key) {
            return $this->prefix . $key;
        }, $keys));

        foreach ($values as $index => $value) {
            $results[$keys[$index]] = ! is_null($value) ? $this->unserialize($value) : null;
        }

        return $results;
    }

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param string $key
     * @param mixed $value
     * @param integer $seconds
     * @return boolean
     */
    public function put($key, $value, $seconds)
    {
        if ($this->connection()->hset($this->prefix . $this->namespace, $key, $this->serialize($value))) {
            return (bool) $this->connection()->expire(
                $this->prefix . $this->namespace,
                $key,
                (int) max(1, $seconds)
            );
        }
        return false;
    }

    /**
     * Store multiple items in the cache for a given number of seconds.
     *
     * @param array $values
     * @param integer $seconds
     * @return boolean
     */
    public function putMany(array $values, $seconds)
    {
        $this->connection()->multi();

        $manyResult = null;

        foreach ($values as $key => $value) {
            $result = $this->put($key, $value, $seconds);

            $manyResult = is_null($manyResult) ? $result : $result && $manyResult;
        }

        $this->connection()->exec();

        return $manyResult ?: false;
    }

    /**
     * Store an item in the cache if the key doesn't exist.
     *
     * @todo Verify this works.
     *
     * @param string $key
     * @param mixed $value
     * @param integer $seconds
     *
     * @return boolean
     */
    public function add(string $key, $value, int $seconds)
    {
        $lua = "return redis.call('exists',KEYS[1])<1 and redis.call('setex',KEYS[1],ARGV[2],ARGV[1])";

        return (bool) $this->connection()->eval(
            $lua,
            1,
            $this->prefix . $key,
            $this->serialize($value),
            (int) max(1, $seconds)
        );
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return integer
     */
    public function increment($key, $value = 1)
    {
        return $this->connection()->hincrby($this->prefix . $this->namespace, $key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return integer
     */
    public function decrement($key, $value = 1)
    {
        return $this->connection()->hdecrby($this->prefix . $this->namespace, $key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return boolean
     */
    public function forever($key, $value)
    {
        return (bool) $this->connection()->hset($this->prefix . $this->namespace, $key, $this->serialize($value));
    }

    /**
     * Get a lock instance.
     *
     * @param string $name
     * @param integer $seconds
     * @param string|null $owner
     *
     * @return \Illuminate\Contracts\Cache\Lock
     */
    public function lock($name, $seconds = 0, $owner = null)
    {
        return new RedisLock($this->connection(), $this->prefix . $this->namespace . $name, $seconds, $owner);
    }

    /**
     * Restore a lock instance using the owner identifier.
     *
     * @param string $name
     * @param string $owner
     *
     * @return \Illuminate\Contracts\Cache\Lock
     */
    public function restoreLock($name, $owner)
    {
        return $this->lock($name, 0, $owner);
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     *
     * @return boolean
     */
    public function forget($key)
    {
        return (bool) $this->connection()->hdel($this->prefix . $this->namespace, $key);
    }

    /**
     * Remove all items from the namespaced cache.
     *
     * @return boolean
     */
    public function flush()
    {
        $this->connection()->del($this->prefix . $this->namespace);

        return true;
    }

    /**
     * Get the Redis connection instance.
     *
     * @return \Illuminate\Redis\Connections\Connection
     */
    public function connection()
    {
        return $this->redis->connection($this->connection);
    }

    /**
     * Set the connection name to be used.
     *
     * @param string $connection
     *
     * @return void
     */
    public function setConnection(string $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get the Redis database instance.
     *
     * @return \Illuminate\Contracts\Redis\Factory
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set the cache key prefix.
     *
     * @param string $prefix
     *
     * @return void
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = ! empty($prefix) ? $prefix . ':' : '';
    }

    /**
     * Serialize the value.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function serialize($value)
    {
        return is_numeric($value) && ! in_array($value, [INF, -INF]) && ! is_nan($value) ? $value : serialize($value);
    }

    /**
     * Unserialize the value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function unserialize($value)
    {
        return is_numeric($value) ? $value : unserialize($value);
    }

    /**
     * Get all items cached in current namespace
     *
     * @return null
     */
    public function getAll()
    {
        return $this->connection()->hgetall($this->prefix . $this->namespace);
    }

    /**
     * Sets the current namespace
     *
     * @param string $namespace
     *
     * @return $this
     */
    public function namespace(string $namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }
}
