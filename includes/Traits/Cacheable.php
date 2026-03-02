<?php

declare(strict_types=1);

namespace AMF\Traits;

/**
 * Cacheable trait - provides caching functionality
 */
trait Cacheable
{
    /**
     * Cache group
     *
     * @var string
     */
    private string $cache_group = 'amf';

    /**
     * Get value from cache
     *
     * @param string $key
     * @param string $group
     * @return mixed|false
     */
    protected function getCache(string $key, string $group = '')
    {
        $group = $group ?: $this->cache_group;
        return wp_cache_get($key, $group);
    }

    /**
     * Set value in cache
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @param string $group
     * @return bool
     */
    protected function setCache(
        string $key,
        $value,
        int $ttl = 0,
        string $group = ''
    ): bool {
        $group = $group ?: $this->cache_group;
        return wp_cache_set($key, $value, $group, $ttl);
    }

    /**
     * Delete value from cache
     *
     * @param string $key
     * @param string $group
     * @return bool
     */
    protected function deleteCache(string $key, string $group = ''): bool
    {
        $group = $group ?: $this->cache_group;
        return wp_cache_delete($key, $group);
    }

    /**
     * Get cached value or compute and cache it
     *
     * @param string $key
     * @param callable $callback
     * @param int $ttl
     * @param string $group
     * @return mixed
     */
    protected function cacheRemember(
        string $key,
        callable $callback,
        int $ttl = 3600,
        string $group = ''
    ) {
        $cached = $this->getCache($key, $group);

        if ($cached !== false) {
            return $cached;
        }

        $value = $callback();
        $this->setCache($key, $value, $ttl, $group);

        return $value;
    }

    /**
     * Flush cache group
     *
     * @param string $group
     * @return bool
     */
    protected function flushCache(string $group = ''): bool
    {
        $group = $group ?: $this->cache_group;
        return wp_cache_flush_group($group);
    }

    /**
     * Generate cache key from components
     *
     * @param string ...$parts
     * @return string
     */
    protected function makeCacheKey(string ...$parts): string
    {
        return implode(':', $parts);
    }
}
