<?php

declare(strict_types=1);

namespace TheChoice\Engine;

use Countable;
use TheChoice\Exception\RuleNotFoundException;

/**
 * Aggregated report of a rule engine run.
 */
final readonly class EngineReport implements Countable
{
    /** @var array<string, RuleResult> */
    private array $results;

    /**
     * @param array<string, RuleResult> $results
     */
    public function __construct(array $results)
    {
        $this->results = $results;
    }

    /**
     * @return array<string, RuleResult>
     */
    public function getFired(): array
    {
        return array_filter($this->results, static fn (RuleResult $ruleResult): bool => $ruleResult->fired);
    }

    /**
     * @return array<string, RuleResult>
     */
    public function getSkipped(): array
    {
        return array_filter($this->results, static fn (RuleResult $ruleResult): bool => !$ruleResult->fired);
    }

    /**
     * @return array<string, RuleResult>
     */
    public function getAll(): array
    {
        return $this->results;
    }

    public function hasFired(string $name): bool
    {
        if (!array_key_exists($name, $this->results)) {
            return false;
        }

        return $this->results[$name]->fired;
    }

    /**
     * @throws RuleNotFoundException
     */
    public function getResult(string $name): RuleResult
    {
        if (!array_key_exists($name, $this->results)) {
            throw new RuleNotFoundException(
                sprintf('Rule "%s" was not part of this engine run', $name),
            );
        }

        return $this->results[$name];
    }

    public function count(): int
    {
        return count($this->results);
    }
}
