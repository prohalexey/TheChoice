<?php

namespace TheChoice\Factory;

use TheChoice\Contracts\BuilderInterface;
use TheChoice\NodeType\Assert;

class NodeAssertFactory
{
    public function build(BuilderInterface $builder, array &$structure): Assert
    {
        self::validate($structure);

        $node = new Assert(
            $builder->build($structure['if']),
            $builder->build($structure['then']),
            $structure['else'] ? $builder->build($structure['else']) : null
        );

        if (self::nodeHasDescription($structure)) {
            $node->setDescription($structure['description']);
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
                throw new \LogicException(sprintf('The "%s" property is absent in node type "assert"!', $key));
            }
        }
    }

    private static function nodeHasDescription(array &$structure): bool
    {
        return array_key_exists('description', $structure) && \is_string($structure['description']);
    }
}