<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

use TheChoice\Builder\BuilderInterface;
use TheChoice\Node\Context;
use TheChoice\Operator\OperatorResolverInterface;

class NodeContextFactory implements NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure): Context
    {
        $node = new Context();

        $node->setRoot($builder->getRoot());

        if (self::nodeHasOperator($structure)) {
            $operatorResolver = $builder->getContainer()->get(OperatorResolverInterface::class);
            $operatorType = $operatorResolver->resolve($structure['operator']);

            $operator = $builder->getContainer()->get($operatorType);
            $operator->setValue($structure['value']);

            $node->setOperator($operator);
        }

        if (self::nodeHasContextName($structure)) {
            $node->setContextName($structure['contextName']);
        }

        if (self::nodeHasDescription($structure)) {
            $node->setDescription($structure['description']);
        }

        if (self::nodeHasPriority($structure)) {
            $node->setPriority((int)$structure['priority']);
        }

        if (self::nodeHasParams($structure)) {
            $node->setParams($structure['params']);
        }

        if (self::nodeHasModifiers($structure)) {
            $node->setModifiers($structure['modifiers']);
        }

        if (self::isNodeStoppable($structure)) {
            $node->setStoppableType(Context::STOP_IMMEDIATELY);
        }

        return $node;
    }

    private static function nodeHasDescription(array &$structure): bool
    {
        return array_key_exists('description', $structure);
    }

    private static function nodeHasPriority(array &$structure): bool
    {
        return array_key_exists('priority', $structure);
    }

    private static function nodeHasParams(array &$structure): bool
    {
        return array_key_exists('params', $structure);
    }

    private static function nodeHasModifiers(array &$structure): bool
    {
        return array_key_exists('modifiers', $structure);
    }

    private static function isNodeStoppable(array &$structure): bool
    {
        return array_key_exists('break', $structure) ;
    }

    private static function nodeHasOperator(array &$structure): bool
    {
        return array_key_exists('operator', $structure) && array_key_exists('value', $structure);
    }

    private static function nodeHasContextName(array &$structure): bool
    {
        return array_key_exists('contextName', $structure);
    }
}