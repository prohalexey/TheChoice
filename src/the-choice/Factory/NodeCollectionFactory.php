<?php

namespace TheChoice\Factory;

use TheChoice\Contracts\BuilderInterface;
use TheChoice\NodeType\AndCollection;
use TheChoice\NodeType\OrCollection;

class NodeCollectionFactory
{
    public function build(BuilderInterface $builder, array &$structure)
    {
        self::validate($structure);

        if ($structure['type'] === 'or') {
            $node = new OrCollection();
        } elseif ($structure['type'] === 'and') {
            $node = new AndCollection();
        } else {
            throw new \LogicException(sprintf('Unknown collection type "%s"', $structure['type']));
        }

        if (self::nodeHasDescription($structure)) {
            $node->setDescription($structure['description']);
        }

        foreach ($structure['elements'] as $element) {
            $node->add($builder->build($element));
        }

        return $node;
    }

    private static function validate(array &$structure)
    {
        $keysThatMustBePresent = [
            'type',
            'elements',
        ];

        foreach ($keysThatMustBePresent as $key) {
            if (!array_key_exists($key, $structure)) {
                throw new \LogicException(sprintf('The "%s" property is absent in node type "collection"!', $key));
            }
        }

        if (!\is_array($structure['elements'])) {
            throw new \LogicException('The "elements" property must be an array!');
        }

        if (!\is_string($structure['type'])) {
            throw new \LogicException(sprintf('Collection type must be "or" or "and". "%s" given', $structure['type']));
        }
    }

    private static function nodeHasDescription(array &$structure): bool
    {
        return array_key_exists('description', $structure) && \is_string($structure['description']);
    }
}