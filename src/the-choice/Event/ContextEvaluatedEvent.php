<?php

declare(strict_types=1);

namespace TheChoice\Event;

/**
 * Dispatched after a context node is evaluated by ContextProcessor.
 *
 * Contains the raw context value, the operator applied, and the final result.
 */
final readonly class ContextEvaluatedEvent
{
    public function __construct(
        public string $contextName,
        public mixed $contextValue,
        public ?string $operatorName,
        public mixed $operatorValue,
        public mixed $result,
    ) {
    }
}
