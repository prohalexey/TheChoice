<?php

declare(strict_types=1);

namespace TheChoice\Engine;

/**
 * Immutable result of a single rule evaluation within an engine run.
 */
final readonly class RuleResult
{
    /**
     * True when the rule evaluation produced a non-null, non-false result.
     *
     * Intentional semantics — the following values are all considered "fired"
     * (i.e. the rule matched and produced a meaningful result):
     *   - any truthy value: true, non-zero int/float, non-empty string, non-empty array
     *   - falsy-but-present values: 0, 0.0, "", []
     *
     * Only null and false are treated as "not fired" (rule skipped / condition not met).
     */
    public bool $fired;

    public function __construct(
        public string $name,
        public mixed $result,
    ) {
        $this->fired = null !== $result && false !== $result;
    }
}
