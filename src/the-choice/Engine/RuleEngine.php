<?php

declare(strict_types=1);

namespace TheChoice\Engine;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use TheChoice\Event\EngineRunAfterEvent;
use TheChoice\Event\EngineRunBeforeEvent;
use TheChoice\Event\RuleErrorEvent;
use TheChoice\Event\RuleFiredEvent;
use TheChoice\Node\Node;
use TheChoice\Processor\RootProcessor;
use TheChoice\Registry\RuleEntry;
use TheChoice\Registry\RuleRegistry;
use Throwable;

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
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
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

        if (null !== $this->eventDispatcher) {
            $processor->setEventDispatcher($this->eventDispatcher);
        }

        $sorted = $this->getSortedEntries();

        $this->eventDispatcher?->dispatch(new EngineRunBeforeEvent($sorted));

        $startTime = hrtime(true);
        $results = [];

        foreach ($sorted as $entry) {
            $ruleStart = hrtime(true);

            try {
                $result = $processor->process($entry->node);
            } catch (Throwable $exception) {
                $this->eventDispatcher?->dispatch(new RuleErrorEvent(
                    ruleName: $entry->name,
                    entry: $entry,
                    exception: $exception,
                ));

                throw $exception;
            }

            $ruleResult = new RuleResult(
                name: $entry->name,
                result: $result,
            );
            $results[$entry->name] = $ruleResult;

            $ruleElapsedMs = (hrtime(true) - $ruleStart) / 1_000_000;

            if ($ruleResult->fired) {
                $this->eventDispatcher?->dispatch(new RuleFiredEvent(
                    ruleName: $entry->name,
                    entry: $entry,
                    result: $ruleResult,
                    elapsedMs: $ruleElapsedMs,
                ));
            }
        }

        $report = new EngineReport($results);

        $totalElapsedMs = (hrtime(true) - $startTime) / 1_000_000;
        $this->eventDispatcher?->dispatch(new EngineRunAfterEvent(
            report: $report,
            elapsedMs: $totalElapsedMs,
        ));

        return $report;
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
