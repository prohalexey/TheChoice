<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

use TheChoice\Builder\BuilderInterface;
use TheChoice\Exception\LogicException;
use TheChoice\Node\Value;

class NodeValueFactory implements NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure): Value
    {
        if (!array_key_exists('value', $structure)) {
            throw new LogicException('The "value" property is absent in node type "Value"!');
        }

        $node = new Value($structure['value']);

        $node->setRoot($builder->getRoot());

        if (array_key_exists('description', $structure)) {
            $description = $structure['description'];
            if (is_string($description)) {
                $node->setDescription($description);
            }
        }

        return $node;
    }
}
