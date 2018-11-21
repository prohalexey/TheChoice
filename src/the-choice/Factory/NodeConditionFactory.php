<?php

namespace TheChoice\Factory;

use TheChoice\Contracts\BuilderInterface;
use TheChoice\NodeType\Condition;

class NodeConditionFactory
{
    public function build(BuilderInterface $builder, array &$structure): Condition
    {
        self::validate($structure);

        $node = new Condition(
            $builder->build($structure['if']),
            $builder->build($structure['then']),
            $structure['else'] ? $builder->build($structure['else']) : null
        );

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
                throw new \LogicException(sprintf('The "%s" property is absent in node type "condition"!', $key));
            }
        }
    }

    private static function nodeHasDescription(array &$structure): bool
    {
        return array_key_exists('description', $structure) && \is_string($structure['description']);
    }

    private static function nodeHasPriority(array &$structure): bool
    {
        return array_key_exists('priority', $structure) && (\is_string($structure['priority'] || \is_numeric($structure['priority'])));
    }
}