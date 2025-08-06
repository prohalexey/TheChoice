<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

use InvalidArgumentException;
use TheChoice\Builder\BuilderInterface;
use TheChoice\Exception\LogicException;
use TheChoice\Node\Root;

class NodeRootFactory implements NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure): Root
    {
        $rootNode = new Root();

        $builder->setRoot($rootNode);

        if (self::nodeHasDescription($structure)) {
            $description = $structure['description'];
            if (is_string($description)) {
                $rootNode->setDescription($description);
            }
        }

        if (self::nodeHasStorage($structure)) {
            $storage = $structure['storage'];
            if (is_array($storage)) {
                foreach ($storage as $key => $value) {
                    if (is_string($key)) {
                        $rootNode->setGlobal($key, $value);
                    }
                }
            }
        }

        if (!self::nodeHasRules($structure)) {
            throw new LogicException('The "rules" property is absent in node type "Root"!');
        }

        $rulesStructure = $structure['rules'];
        if (!is_array($rulesStructure)) {
            throw new InvalidArgumentException('Rules structure must be an array');
        }

        $rules = $builder->build($rulesStructure);
        $rootNode->setRules($rules);

        return $rootNode;
    }

    private static function nodeHasDescription(array $structure): bool
    {
        return array_key_exists('description', $structure);
    }

    private static function nodeHasStorage(array $structure): bool
    {
        return array_key_exists('storage', $structure);
    }

    private static function nodeHasRules(array $structure): bool
    {
        return array_key_exists('rules', $structure) && is_array($structure['rules']);
    }
}
