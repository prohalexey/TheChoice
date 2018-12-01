<?php

namespace TheChoice\Factory;

use TheChoice\Contract\BuilderInterface;
use TheChoice\Contract\NodeFactoryInterface;
use TheChoice\Node\Value;

class NodeValueFactory implements NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure): Value
    {
        self::validate($structure);

        $node = new Value($structure['value']);

        if (self::nodeHasDescription($structure)) {
            $node->setDescription($structure['description']);
        }

        return $node;
    }

    private static function validate(array &$structure)
    {
        if (!array_key_exists('value', $structure)) {
            throw new \LogicException('The "value" property is absent in node type "Value"!');
        }
    }

    private static function nodeHasDescription(array &$structure): bool
    {
        return array_key_exists('description', $structure);
    }
}