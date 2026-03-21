<?php

declare(strict_types=1);

namespace TheChoice\Trace;

/**
 * Represents a single entry in the evaluation trace tree.
 *
 * Each entry records which node was visited, what it was called,
 * what result it produced, and any child entries from nested evaluations.
 */
final class TraceEntry
{
    /** @var array<TraceEntry> */
    private array $children = [];

    public function __construct(
        private readonly string $nodeType,
        private readonly string $nodeName,
        private mixed $result = null,
    ) {
    }

    public function getNodeType(): string
    {
        return $this->nodeType;
    }

    public function getNodeName(): string
    {
        return $this->nodeName;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function setResult(mixed $result): void
    {
        $this->result = $result;
    }

    public function addChild(self $child): void
    {
        $this->children[] = $child;
    }

    /**
     * @return array<TraceEntry>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Renders this entry as a human-readable indented string.
     */
    public function toString(int $indent = 0): string
    {
        $prefix = str_repeat('  ', $indent);
        $resultString = $this->formatResult($this->result);
        $line = sprintf('%s%s[%s] → %s', $prefix, $this->nodeType, $this->nodeName, $resultString);

        $lines = [$line];
        foreach ($this->children as $child) {
            $lines[] = $child->toString($indent + 1);
        }

        return implode("\n", $lines);
    }

    private function formatResult(mixed $value): string
    {
        if (null === $value) {
            return 'null';
        }

        if (true === $value) {
            return 'TRUE';
        }

        if (false === $value) {
            return 'FALSE';
        }

        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }

        if (is_string($value)) {
            return sprintf('"%s"', $value);
        }

        if (is_array($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        }

        return get_debug_type($value);
    }
}
