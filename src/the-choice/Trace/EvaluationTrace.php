<?php

declare(strict_types=1);

namespace TheChoice\Trace;

/**
 * Result of a traced rule evaluation.
 *
 * Contains both the final computed value and the full trace tree
 * that records every node visited during evaluation.
 */
final readonly class EvaluationTrace
{
    public function __construct(
        private mixed $value,
        private TraceEntry $trace,
    ) {
    }

    /**
     * Returns the final evaluation result (same as a normal process() call).
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Returns the root TraceEntry representing the full evaluation tree.
     */
    public function getTrace(): TraceEntry
    {
        return $this->trace;
    }

    /**
     * Returns a human-readable explanation of the evaluation.
     */
    public function explain(): string
    {
        return $this->trace->toString();
    }
}
