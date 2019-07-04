<?php

namespace TheChoice\Builder;

use Symfony\Component\Yaml\Yaml;

class YamlBuilder extends ArrayBuilder
{
    public function parse(string $content)
    {
        $structure = Yaml::parse($content);

        return $this->build($structure);
    }

    public function parseFile(string $filename)
    {
        $filename = $this->getRootDir() . $filename;

        if (!file_exists($filename)) {
            throw new \InvalidArgumentException(
                sprintf('File "%s" not found', $filename)
            );
        }

        $content = file_get_contents($filename);
        if ($content === false) {
            throw new \InvalidArgumentException(
                sprintf('File "%s" not found', $filename)
            );
        }

        return $this->parse($content);
    }
}
