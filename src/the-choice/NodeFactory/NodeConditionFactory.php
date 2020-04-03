<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

use TheChoice\Builder\BuilderInterface;
use TheChoice\Node\Condition;

use TheChoice\Exception\LogicException;

class NodeConditionFactory implements NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure): Condition
    {
        self::validate($structure);

        $node = new Condition(
            $builder->build($structure['if']),
            $builder->build($structure['then']),
            self::nodeHasElseBranch($structure) ? $builder->build($structure['else']) : null
        );

        $node->setRoot($builder->getRoot());

        if (self::nodeHasDescription($structure)) {
            $node->setDescription($structure['description']);
        }

        if (self::nodeHasPriority($structure)) {
            $node->setPriority((int)$structure['priority']);
        }

        return $node;
    }

    private static function validate(array &$structure)
    {
        $keysThatMustBePresent = [
            'if',
            'then',
        ];

        foreach ($keysThatMustBePresent as $key) {
            if (!array_key_exists($key, $structure)) {
                throw new LogicException(sprintf('The "%s" property is absent in node type "Condition"!', $key));
            }
        }
    }

    private static function nodeHasElseBranch(array &$structure): bool
    {
        return array_key_exists('else', $structure);
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