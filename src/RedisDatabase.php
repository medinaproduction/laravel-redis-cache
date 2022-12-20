<?php

namespace MedinaProduction\RedisCache;

use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\RedisManager;

abstract class RedisDatabase
{
    /**
     * Redis server.
     */
    protected RedisManager $redis;

    /**
     * Redis connection to use.
     */
    protected string $connection;

    /**
     * String to prepend to keys.
     */
    protected string $prefix;

    /**
     * Assign dependencies.
     *
     * @param RedisManager $redis
     * @param string $conn
     * @param string $prefix
     */
    public function __construct(RedisManager $redis, string $conn = 'default', string $prefix = '')
    {
        $this->redis = $redis;
        $this->setConnection($conn);
        $this->setPrefix($prefix);
    }

    /**
     * Get Redis connection instance.
     *
     * @return Connection
     */
    protected function connection()
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
     * Get cache key prefix.
     *
     * @return string
     */
    protected function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set cache key prefix.
     *
     * @param string $prefix
     *
     * @return void
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = !empty($prefix) ? $prefix . ':' : '';
    }

    /**
     * Find all keys matching pattern.
     *
     * @param string $pattern
     * @param boolean $usePrefix
     *
     * @return array
     */
    public function keys(string $pattern, bool $usePrefix = true): array
    {
        $pattern = $usePrefix ? $this->prefix . $pattern : $pattern;

        return $this->connection()->keys($pattern);
    }

    /**
     * Removes the specified key(s).
     *
     * @param mixed|array $keys
     *
     * @return integer
     */
    public function del($keys)
    {
        return $this->connection()->del($keys);
    }

    /**
     * Returns all field names in the hash stored at key.
     *
     * @param string $key
     *
     * @return array
     */
    public function hashKeys(string $key)
    {
        return $this->connection()->hkeys($this->prefix . $key);
    }

    /**
     * Removes the specified key(s) from hash.
     *
     * @param string $key
     * @param mixed|array  $fields
     *
     * @return integer
     */
    public function hashDel(string $key, $fields): int
    {
        return $this->connection()->hdel($this->prefix . $key, $fields);
    }
}
