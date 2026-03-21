<?php

declare(strict_types=1);

namespace TheChoice\Trace;

/**
 * Stack-based collector for building trace trees during rule evaluation.
 *
 * Usage:
 *  1. Call begin() before processing a node — pushes a new TraceEntry onto the stack.
 *  2. Call end($result) after processing — pops the entry, sets its result,
 *     and attaches it as a child of the new current (parent) entry.
 *  3. When done, getRoot() returns the top-level entry with the full trace tree.
 */
final class TraceCollector
{
    /** @var array<TraceEntry> */
    private array $stack = [];

    private ?TraceEntry $root = null;

    public function begin(string $nodeType, string $nodeName): void
    {
        $entry = new TraceEntry($nodeType, $nodeName);
        $this->stack[] = $entry;
    }

    public function end(mixed $result): void
    {
        $entry = array_pop($this->stack);
        if (null === $entry) {
            return;
        }

        $entry->setResult($result);

        if ([] !== $this->stack) {
            $this->stack[array_key_last($this->stack)]->addChild($entry);
        } else {
            $this->root = $entry;
        }
    }

    public function isActive(): bool
    {
        return [] !== $this->stack;
    }

    public function getRoot(): ?TraceEntry
    {
        return $this->root;
    }
}
