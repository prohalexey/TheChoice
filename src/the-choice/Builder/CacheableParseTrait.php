<?php

declare(strict_types=1);

namespace TheChoice\Builder;

use DateInterval;
use Psr\SimpleCache\CacheInterface;
use TheChoice\Node\Node;

/**
 * Shared caching logic for CachedJsonBuilder and CachedYamlBuilder.
 *
 * Serializes and caches the fully built node tree keyed by the MD5 of the
 * input content, so identical rule definitions are parsed only once.
 */
trait CacheableParseTrait
{
    private readonly CacheInterface $cache;

    private readonly null|int|DateInterval $ttl;

    private readonly string $keyPrefix;

    /**
     * Looks up the cache for a previously built node tree.
     * Returns the deserialized Node on hit, or null on miss / corrupted entry.
     */
    private function getFromCache(string $content): ?Node
    {
        $key = $this->makeCacheKey($content);
        $cached = $this->cache->get($key);

        if (!is_string($cached)) {
            return null;
        }

        set_error_handler(static fn (): bool => true);
        $node = unserialize($cached);
        restore_error_handler();

        if ($node instanceof Node) {
            return $node;
        }

        return null;
    }

    /**
     * Stores a built node tree in the cache.
     */
    private function storeInCache(string $content, Node $node): void
    {
        $this->cache->set($this->makeCacheKey($content), serialize($node), $this->ttl);
    }

    private function makeCacheKey(string $content): string
    {
        return $this->keyPrefix . md5($content);
    }
}
