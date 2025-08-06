<?php

declare(strict_types=1);

namespace TheChoice\Builder;

use Symfony\Component\Yaml\Yaml;
use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Node\Node;

class YamlBuilder extends ArrayBuilder
{
    public function parse(string $content): Node
    {
        $decoded = Yaml::parse($content);
        if (!is_array($decoded)) {
            throw new InvalidArgumentException('YAML must parse to an array');
        }

        return $this->build($decoded);
    }

    public function parseFile(string $filename): mixed
    {
        if (!file_exists($filename)) {
            throw new InvalidArgumentException(sprintf('File "%s" not found', $filename));
        }

        $content = file_get_contents($filename);
        if (false === $content) {
            throw new InvalidArgumentException(sprintf('File "%s" not found', $filename));
        }

        return $this->parse($content);
    }
}
