<?php

declare(strict_types=1);

namespace TheChoice\Registry;

use Countable;
use TheChoice\Exception\DuplicateRuleException;
use TheChoice\Exception\RuleNotFoundException;
use TheChoice\Node\Node;

/**
 * Central registry for named rules with metadata (tags, priority, version).
 */
class RuleRegistry implements Countable
{
    /** @var array<string, RuleEntry> */
    private array $entries = [];

    /**
     * @param array<string> $tags
     *
     * @throws DuplicateRuleException
     */
    public function register(
        string $name,
        Node $node,
        array $tags = [],
        int $priority = 0,
        string $version = '1.0',
        string $description = '',
    ): void {
        if (array_key_exists($name, $this->entries)) {
            throw new DuplicateRuleException(
                sprintf('Rule "%s" is already registered', $name),
            );
        }

        $this->entries[$name] = new RuleEntry(
            name: $name,
            node: $node,
            tags: $tags,
            priority: $priority,
            version: $version,
            description: $description,
        );
    }

    /**
     * @throws RuleNotFoundException
     */
    public function get(string $name): RuleEntry
    {
        if (!array_key_exists($name, $this->entries)) {
            throw new RuleNotFoundException(
                sprintf('Rule "%s" is not registered', $name),
            );
        }

        return $this->entries[$name];
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->entries);
    }

    /**
     * @throws RuleNotFoundException
     */
    public function remove(string $name): void
    {
        if (!array_key_exists($name, $this->entries)) {
            throw new RuleNotFoundException(
                sprintf('Rule "%s" is not registered', $name),
            );
        }

        unset($this->entries[$name]);
    }

    /**
     * Returns entries matching the given tag.
     *
     * @return array<RuleEntry>
     */
    public function findByTag(string $tag): array
    {
        $result = [];

        foreach ($this->entries as $entry) {
            if (in_array($tag, $entry->tags, true)) {
                $result[] = $entry;
            }
        }

        return $result;
    }

    /**
     * Returns all entries sorted by priority descending (highest first).
     *
     * @return array<RuleEntry>
     */
    public function all(): array
    {
        $entries = array_values($this->entries);

        usort($entries, static fn (RuleEntry $a, RuleEntry $b): int => $b->priority <=> $a->priority);

        return $entries;
    }

    public function count(): int
    {
        return count($this->entries);
    }
}
