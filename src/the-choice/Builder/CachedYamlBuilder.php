<?php

declare(strict_types=1);

namespace TheChoice\Builder;

use DateInterval;
use Override;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use TheChoice\Node\Node;

/**
 * A caching decorator for YamlBuilder.
 *
 * Usage:
 *   $builder = new CachedYamlBuilder($container, $cache, ttl: 3600);
 *   $node = $builder->parseFile('rules/discount.yaml');
 */
class CachedYamlBuilder extends YamlBuilder
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

    #[Override]
    public function parse(string $content): Node
    {
        $cached = $this->getFromCache($content);
        if ($cached instanceof Node) {
            return $cached;
        }

        $node = parent::parse($content);
        $this->storeInCache($content, $node);

        return $node;
    }
}
