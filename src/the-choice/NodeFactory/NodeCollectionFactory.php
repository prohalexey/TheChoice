<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

use InvalidArgumentException;
use TheChoice\Builder\BuilderInterface;
use TheChoice\Node\Collection;

class NodeCollectionFactory implements NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure): Collection
    {
        $type = $structure['type'];
        if (!is_string($type)) {
            throw new InvalidArgumentException('Type must be a string');
        }

        $node = new Collection($type);
        $node->setRoot($builder->getRoot());

        if (self::nodeHasDescription($structure)) {
            $description = $structure['description'];
            if (is_string($description)) {
                $node->setDescription($description);
            }
        }

        if (self::nodeHasPriority($structure)) {
            $priority = $structure['priority'];
            if (is_numeric($priority)) {
                $node->setPriority((int)$priority);
            }
        }

        if (self::nodeHasCount($structure)) {
            $count = $structure['count'];
            if (is_numeric($count)) {
                $node->setCount((int)$count);
            }
        }

        if (self::nodeHasChildNodes($structure)) {
            $nodes = $structure['nodes'];
            if (is_array($nodes)) {
                foreach ($nodes as $element) {
                    if (is_array($element)) {
                        $builtNode = $builder->build($element);
                        $node->add($builtNode);
                    }
                }
            }
        }

        return $node;
    }

    private static function nodeHasDescription(array $structure): bool
    {
        return array_key_exists('description', $structure);
    }

    private static function nodeHasPriority(array $structure): bool
    {
        return array_key_exists('priority', $structure);
    }

    private static function nodeHasCount(array $structure): bool
    {
        return array_key_exists('count', $structure);
    }

    private static function nodeHasChildNodes(array $structure): bool
    {
        return array_key_exists('nodes', $structure) && is_array($structure['nodes']);
    }
}
