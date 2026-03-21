<?php

declare(strict_types=1);

namespace TheChoice\Event;

use TheChoice\Engine\RuleResult;
use TheChoice\Registry\RuleEntry;

/**
 * Dispatched when a rule fires (result is neither null nor false).
 */
final readonly class RuleFiredEvent
{
    public function __construct(
        public string $ruleName,
        public RuleEntry $entry,
        public RuleResult $result,
        public float $elapsedMs,
    ) {
    }
}
