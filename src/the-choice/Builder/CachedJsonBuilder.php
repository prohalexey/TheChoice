<?php

declare(strict_types=1);

namespace TheChoice\Builder;

use DateInterval;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use TheChoice\Node\Node;

/**
 * A caching decorator for JsonBuilder.
 *
 * Usage:
 *   $builder = new CachedJsonBuilder($container, $cache, ttl: 3600);
 *   $node = $builder->parseFile('rules/discount.json');
 */
class CachedJsonBuilder extends JsonBuilder
{
    use CacheableParseTrait;

    public function __construct(
        ContainerInterface $container,
        CacheInterface $cache,
        null|int|DateInterval $ttl = null,
        string $keyPrefix = 'the-choice.',
    ) {
        parent::__construct($container);
        $this->cache = $cache;
        $this->ttl = $ttl;
        $this->keyPrefix = $keyPrefix;
    }

    #[\Override]
    public function parse(string $jsonSettings, int $maxDepth = 512, int $options = 0): Node
    {
        $cached = $this->getFromCache($jsonSettings);
        if ($cached instanceof Node) {
            return $cached;
        }

        $node = parent::parse($jsonSettings, $maxDepth, $options);
        $this->storeInCache($jsonSettings, $node);

        return $node;
    }
}
