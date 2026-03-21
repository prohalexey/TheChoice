<?php

declare(strict_types=1);

namespace TheChoice\Registry;

use TheChoice\Node\Node;

/**
 * Immutable value object representing a named rule with metadata.
 */
final readonly class RuleEntry
{
    /**
     * @param array<string> $tags
     */
    public function __construct(
        public string $name,
        public Node $node,
        public array $tags = [],
        public int $priority = 0,
        public string $version = '1.0',
        public string $description = '',
    ) {
    }
}
