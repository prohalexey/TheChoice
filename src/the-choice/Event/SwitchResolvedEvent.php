<?php

declare(strict_types=1);

namespace TheChoice\Event;

/**
 * Dispatched after a switch node is resolved by SwitchProcessor.
 *
 * A null matchedCaseIndex indicates the default branch was taken.
 */
final readonly class SwitchResolvedEvent
{
    public function __construct(
        public string $contextName,
        public mixed $contextValue,
        public ?int $matchedCaseIndex,
        public mixed $result,
    ) {
    }
}
