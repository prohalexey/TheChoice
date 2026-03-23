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

        if (array_key_exists('description', $structure)) {
            $description = $structure['description'];
            if (is_string($description)) {
                $node->setDescription($description);
            }
        }

        if (array_key_exists('priority', $structure)) {
            $priority = $structure['priority'];
            if (is_numeric($priority)) {
                $node->setPriority((int)$priority);
            }
        }

        if (array_key_exists('count', $structure)) {
            $count = $structure['count'];
            if (is_numeric($count)) {
                $node->setCount((int)$count);
            }
        }

        if (array_key_exists('nodes', $structure) && is_array($structure['nodes'])) {
            $nodes = $structure['nodes'];
            foreach ($nodes as $element) {
                if (is_array($element)) {
                    $builtNode = $builder->build($element);
                    $node->add($builtNode);
                }
            }
        }

        return $node;
    }
}
