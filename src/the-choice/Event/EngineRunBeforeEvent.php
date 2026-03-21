<?php

declare(strict_types=1);

namespace TheChoice\Event;

use TheChoice\Registry\RuleEntry;

/**
 * Dispatched before RuleEngine starts processing all rules.
 */
final readonly class EngineRunBeforeEvent
{
    /**
     * @param array<RuleEntry> $rules sorted by priority (highest first)
     */
    public function __construct(
        public array $rules,
    ) {
    }
}
