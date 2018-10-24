<?php

namespace TheChoice;

use TheChoice\Contracts\RuleCollectionBuilderInterface;

class JsonRuleCollectionBuilder
{
    private $_collectionBuilder;

    public function __construct(RuleCollectionBuilderInterface $collectionBuilder)
    {
        $this->_collectionBuilder = $collectionBuilder;
    }

    public function parse(string $jsonSettings, $maxDepth = 512, $options = 0): Collection
    {
        $structure = json_decode($jsonSettings, true, $maxDepth, $options);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(json_last_error_msg());
        }

        return $this->_collectionBuilder->build($structure);
    }
}
