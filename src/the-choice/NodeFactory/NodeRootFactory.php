<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

use TheChoice\Builder\BuilderInterface;
use TheChoice\Exception\LogicException;
use TheChoice\Node\Root;

class NodeRootFactory implements NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure): Root
    {
        $rootNode = new Root();

        $builder->setRoot($rootNode);

        if (array_key_exists('description', $structure)) {
            $description = $structure['description'];
            if (is_string($description)) {
                $rootNode->setDescription($description);
            }
        }

        if (array_key_exists('storage', $structure)) {
            $storage = $structure['storage'];
            if (is_array($storage)) {
                foreach ($storage as $key => $value) {
                    if (is_string($key)) {
                        $rootNode->setGlobal($key, $value);
                    }
                }
            }
        }

        if (!array_key_exists('rules', $structure) || !is_array($structure['rules'])) {
            throw new LogicException('The "rules" property is absent in node type "Root"!');
        }

        $rules = $builder->build($structure['rules']);
        $rootNode->setRules($rules);

        return $rootNode;
    }
}
