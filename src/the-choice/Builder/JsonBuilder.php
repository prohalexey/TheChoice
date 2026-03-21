<?php

declare(strict_types=1);

namespace TheChoice\Builder;

use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Node\Node;

class JsonBuilder extends ArrayBuilder
{
    public function parse(string $jsonSettings, int $maxDepth = 512, int $options = 0): Node
    {
        $this->nodesCount = 0;

        if ($maxDepth < 1) {
            throw new InvalidArgumentException('Max depth must be at least 1');
        }

        $decoded = json_decode($jsonSettings, true, $maxDepth, $options);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(json_last_error_msg());
        }

        if (!is_array($decoded)) {
            throw new InvalidArgumentException('JSON must decode to an array');
        }

        return $this->build($decoded);
    }

    public function parseFile(string $filename, int $maxDepth = 512, int $options = 0): Node
    {
        if (!file_exists($filename)) {
            throw new InvalidArgumentException(sprintf('File "%s" not found', $filename));
        }

        $content = file_get_contents($filename);
        if (false === $content) {
            throw new InvalidArgumentException(sprintf('File "%s" not found', $filename));
        }

        return $this->parse($content, $maxDepth, $options);
    }
}
