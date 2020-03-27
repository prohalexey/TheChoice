<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

use TheChoice\Builder\BuilderInterface;
use TheChoice\Node\Collection;

class NodeCollectionFactory implements NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure): Collection
    {
        $node = new Collection($structure['type']);

        $node->setRoot($builder->getRoot());

        if (self::nodeHasDescription($structure)) {
            $node->setDescription($structure['description']);
        }

        if (self::nodeHasPriority($structure)) {
            $node->setPriority((int)$structure['priority']);
        }

        if (self::nodeHasChildNodes($structure)) {
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

    private static function nodeHasChildNodes(array &$structure): bool
    {
        return array_key_exists('nodes', $structure) && is_array($structure['nodes']);
    }
}