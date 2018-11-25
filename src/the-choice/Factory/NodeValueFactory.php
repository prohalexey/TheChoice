<?php

namespace TheChoice\Factory;

use TheChoice\Contracts\BuilderInterface;
use TheChoice\Contracts\NodeFactoryInterface;
use TheChoice\NodeType\Value;

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
        $keysThatMustBePresent = [
            'value',
        ];

        foreach ($keysThatMustBePresent as $key) {
            if (!array_key_exists($key, $structure)) {
                throw new \LogicException(sprintf('The "%s" property is absent in node type "value"!', $key));
            }
        }
    }

    private static function nodeHasDescription(array &$structure): bool
    {
        return array_key_exists('description', $structure) && \is_string($structure['description']);
    }
}