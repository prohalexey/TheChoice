<?php

declare(strict_types=1);

namespace TheChoice\Engine;

use Psr\Container\ContainerInterface;
use TheChoice\Node\Node;
use TheChoice\Processor\RootProcessor;
use TheChoice\Registry\RuleEntry;
use TheChoice\Registry\RuleRegistry;

/**
 * Evaluates multiple rules in a single run and returns an aggregated report.
 *
 * Rules are executed in priority order (highest first).
 */
class RuleEngine
{
    /** @var array<string, RuleEntry> */
    private array $entries = [];

    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    /**
     * Adds a rule by name and node with an optional priority.
     */
    public function addRule(string $name, Node $node, int $priority = 0): void
    {
        $this->entries[$name] = new RuleEntry(
            name: $name,
            node: $node,
            priority: $priority,
        );
    }

    /**
     * Adds a pre-built RuleEntry directly.
     */
    public function addEntry(RuleEntry $entry): void
    {
        $this->entries[$entry->name] = $entry;
    }

    /**
     * Loads all rules from a registry into the engine.
     */
    public function loadFromRegistry(RuleRegistry $registry): void
    {
        foreach ($registry->all() as $entry) {
            $this->entries[$entry->name] = $entry;
        }
    }

    /**
     * Executes all registered rules in priority order (highest first)
     * and returns an aggregated report.
     */
    public function run(): EngineReport
    {
        /** @var RootProcessor $processor */
        $processor = $this->container->get(RootProcessor::class);

        $sorted = $this->getSortedEntries();
        $results = [];

        foreach ($sorted as $entry) {
            $result = $processor->process($entry->node);
            $results[$entry->name] = new RuleResult(
                name: $entry->name,
                result: $result,
            );
        }

        return new EngineReport($results);
    }

    /**
     * Removes all registered rules.
     */
    public function clear(): void
    {
        $this->entries = [];
    }

    /**
     * @return array<RuleEntry>
     */
    private function getSortedEntries(): array
    {
        $entries = array_values($this->entries);

        usort($entries, static fn (RuleEntry $a, RuleEntry $b): int => $b->priority <=> $a->priority);

        return $entries;
    }
}
