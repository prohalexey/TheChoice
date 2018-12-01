<?php

namespace TheChoice\Factory;

use TheChoice\Contract\BuilderInterface;
use TheChoice\Contract\NodeFactoryInterface;
use TheChoice\Node\Collection;

class NodeCollectionFactory implements NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure)
    {
        $node = new Collection($structure['type']);

        if (self::nodeHasDescription($structure)) {
            $node->setDescription($structure['description']);
        }

        if (self::nodeHasPriority($structure)) {
            $node->setPriority((int)$structure['priority']);
        }

        if (\is_array($structure['nodes'])) {
            foreach ($structure['nodes'] as $element) {
                $node->add($builder->build($element));
            }
        }

        return $node;
    }

    private static function nodeHasDescription(array &$structure): bool
    {
        return array_key_exists('description', $structure);
    }

    private static function nodeHasPriority(array &$structure): bool
    {
        return array_key_exists('priority', $structure);
    }
}