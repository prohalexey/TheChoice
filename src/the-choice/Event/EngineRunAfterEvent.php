<?php

declare(strict_types=1);

namespace TheChoice\Event;

use TheChoice\Engine\EngineReport;

/**
 * Dispatched after RuleEngine finishes processing all rules.
 */
final readonly class EngineRunAfterEvent
{
    public function __construct(
        public EngineReport $report,
        public float $elapsedMs,
    ) {
    }
}
