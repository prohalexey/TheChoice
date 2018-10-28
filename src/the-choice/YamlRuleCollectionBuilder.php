<?php

namespace TheChoice;

use Symfony\Component\Yaml\Yaml;
use TheChoice\Contracts\RuleCollectionBuilderInterface;

class YamlRuleCollectionBuilder
{
    private $_collectionBuilder;

    public function __construct(RuleCollectionBuilderInterface $collectionBuilder)
    {
        $this->_collectionBuilder = $collectionBuilder;
    }

    public function parse(string $content): Collection
    {
        $structure = Yaml::parse($content);

        return $this->_collectionBuilder->build($structure);
    }
}
