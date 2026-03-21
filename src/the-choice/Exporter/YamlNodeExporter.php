<?php

declare(strict_types=1);

namespace TheChoice\Exporter;

use Symfony\Component\Yaml\Yaml;
use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Node\Node;

/**
 * Exports a Node tree to a YAML string or file.
 *
 * The $inline parameter controls at which nesting depth the output switches
 * from block style to inline (flow) style. Increase it for deeply nested trees.
 *
 * Example:
 * ```php
 * $exporter = new YamlNodeExporter(new NodeSerializer());
 * $yaml     = $exporter->export($node);              // default: inline=4, indent=2
 * $exporter->exportToFile($node, '/path/to/rule.yaml');
 * ```
 */
final readonly class YamlNodeExporter
{
    public function __construct(private NodeSerializer $serializer)
    {
    }

    /**
     * Serialises the node tree to a YAML string.
     *
     * @param int $inline Nesting depth at which YAML switches to inline/flow style (default: 4)
     * @param int $indent Number of spaces per indentation level (default: 2)
     */
    public function export(Node $node, int $inline = 4, int $indent = 2): string
    {
        return Yaml::dump(
            $this->serializer->toArray($node),
            $inline,
            $indent,
            Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK,
        );
    }

    /**
     * Serialises the node tree and writes the result to a file.
     *
     * @throws InvalidArgumentException when the file cannot be written
     */
    public function exportToFile(
        Node $node,
        string $filename,
        int $inline = 4,
        int $indent = 2,
    ): void {
        $yaml = $this->export($node, $inline, $indent);

        if (false === file_put_contents($filename, $yaml)) {
            throw new InvalidArgumentException(
                sprintf('Failed to write YAML to file "%s"', $filename),
            );
        }
    }
}
