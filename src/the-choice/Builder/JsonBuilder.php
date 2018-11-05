<?php

namespace TheChoice\Builder;

class JsonBuilder extends ArrayBuilder
{
    public function parse(string $jsonSettings, $maxDepth = 512, $options = 0)
    {
        $structure = json_decode($jsonSettings, true, $maxDepth, $options);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(json_last_error_msg());
        }

        return $this->build($structure);
    }

    public function parseFile(string $filename, $maxDepth = 512, $options = 0)
    {
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

        return $this->parse($content, $maxDepth, $options);
    }
}
