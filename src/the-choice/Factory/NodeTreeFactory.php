<?php

namespace TheChoice\Factory;

use TheChoice\Contract\BuilderInterface;
use TheChoice\Contract\NodeFactoryInterface;
use TheChoice\Node\Tree;

class NodeTreeFactory implements NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure): Tree
    {
        $node = new Tree();

        if (self::nodeHasDescription($structure)) {
            $node->setDescription($structure['description']);
        }

        if (self::nodeHasStorage($structure)) {
            foreach ($structure['storage'] as $key => $value) {
                $node->setGlobal($key, $value);
            }
        }

        return $node;
    }

    public function buildNodes(BuilderInterface $builder, array &$structure)
    {
        if (!array_key_exists('nodes', $structure)) {
            throw new \LogicException('The "nodes" property is absent in node type "Tree"!');
        }
        return $builder->build($structure['nodes']);
    }

    private static function nodeHasDescription(array &$structure): bool
    {
        return array_key_exists('description', $structure);
    }

    private static function nodeHasStorage(array &$structure): bool
    {
        return array_key_exists('storage', $structure);
    }
}