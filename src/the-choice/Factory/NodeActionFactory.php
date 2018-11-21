<?php

namespace TheChoice\Factory;

use TheChoice\Contracts\BuilderInterface;
use TheChoice\NodeType\Action;

class NodeActionFactory
{
    public function build(BuilderInterface $builder, array &$structure): Action
    {
        self::validate($structure);

        $node = new Action($structure['action']);

        if (self::nodeHasDescription($structure)) {
            $node->setDescription($structure['description']);
        }

        if (self::nodeHasPriority($structure)) {
            $node->setPriority((int)$structure['priority']);
        }

        if (self::isNodeStoppable($structure)) {
            $node->setStoppableType(Action::STOP_ALWAYS);
        }

        return $node;
    }

    private static function validate(array &$structure)
    {
        $keysThatMustBePresent = [
            'action',
        ];

        foreach ($keysThatMustBePresent as $key) {
            if (!array_key_exists($key, $structure)) {
                throw new \LogicException(sprintf('The "%s" property is absent in node type "action"!', $key));
            }
        }
    }

    private static function nodeHasDescription(array &$structure): bool
    {
        return array_key_exists('description', $structure) && \is_string($structure['description']);
    }

    private static function isNodeStoppable(array &$structure): bool
    {
        return array_key_exists('break', $structure) && \is_string($structure['break']);
    }

    private static function nodeHasPriority(array &$structure): bool
    {
        return array_key_exists('priority', $structure) && (\is_string($structure['priority'] || \is_numeric($structure['priority'])));
    }
}