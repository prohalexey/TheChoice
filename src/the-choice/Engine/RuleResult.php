<?php

declare(strict_types=1);

namespace TheChoice\Engine;

/**
 * Immutable result of a single rule evaluation within an engine run.
 */
final readonly class RuleResult
{
    public bool $fired;

    public function __construct(
        public string $name,
        public mixed $result,
    ) {
        $this->fired = null !== $result && false !== $result;
    }
}
