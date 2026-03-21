<?php

declare(strict_types=1);

namespace TheChoice\Exporter;

use JsonException;
use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Node\Node;

/**
 * Exports a Node tree to a JSON string or file.
 *
 * Example:
 * ```php
 * $exporter = new JsonNodeExporter(new NodeSerializer());
 * $json     = $exporter->export($node);                       // pretty-printed
 * $compact  = $exporter->export($node, pretty: false);        // compact
 * $exporter->exportToFile($node, '/path/to/rule.json');
 * ```
 */
final readonly class JsonNodeExporter
{
    public function __construct(private NodeSerializer $serializer)
    {
    }

    /**
     * Serialises the node tree to a JSON string.
     *
     * @param bool $pretty Enables JSON_PRETTY_PRINT (default: true)
     *
     * @throws InvalidArgumentException when json_encode fails
     */
    public function export(Node $node, bool $pretty = true): string
    {
        $flags = JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        if ($pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }

        try {
            return json_encode($this->serializer->toArray($node), $flags);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException(
                sprintf('Failed to encode node tree to JSON: %s', $exception->getMessage()),
                previous: $exception,
            );
        }
    }

    /**
     * Serialises the node tree and writes the result to a file.
     *
     * @throws InvalidArgumentException when the file cannot be written or encoding fails
     */
    public function exportToFile(Node $node, string $filename, bool $pretty = true): void
    {
        $json = $this->export($node, $pretty);

        if (false === file_put_contents($filename, $json)) {
            throw new InvalidArgumentException(
                sprintf('Failed to write JSON to file "%s"', $filename),
            );
        }
    }
}
