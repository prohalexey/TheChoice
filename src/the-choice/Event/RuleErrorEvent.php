<?php

declare(strict_types=1);

namespace TheChoice\Event;

use TheChoice\Registry\RuleEntry;
use Throwable;

/**
 * Dispatched when a rule throws an exception during evaluation.
 */
final readonly class RuleErrorEvent
{
    public function __construct(
        public string $ruleName,
        public RuleEntry $entry,
        public Throwable $exception,
    ) {
    }
}
