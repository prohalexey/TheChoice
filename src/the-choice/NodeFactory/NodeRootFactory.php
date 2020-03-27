<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

use TheChoice\Builder\BuilderInterface;
use TheChoice\Node\Root;

use TheChoice\Exception\LogicException;

class NodeRootFactory implements NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure): Root
    {
        $rootNode = new Root();

        $builder->setRoot($rootNode);

        if (self::nodeHasDescription($structure)) {
            $rootNode->setDescription($structure['description']);
        }

        if (self::nodeHasStorage($structure)) {
            foreach ($structure['storage'] as $key => $value) {
                $rootNode->setGlobal($key, $value);
            }
        }

        if (!self::nodeHasRules($structure)) {
            throw new LogicException('The "rules" property is absent in node type "Root"!');
        }

        $rootNode->setRules($builder->build($structure['rules']));

        return $rootNode;
    }

    private static function nodeHasDescription(array &$structure): bool
    {
        return array_key_exists('description', $structure);
    }

    private static function nodeHasStorage(array &$structure): bool
    {
        return array_key_exists('storage', $structure);
    }

    private static function nodeHasRules(array &$structure): bool
    {
        return array_key_exists('rules', $structure) && is_array($structure['rules']);
    }
}