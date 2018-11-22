<?php

namespace TheChoice\Factory;

use TheChoice\Contracts\BuilderInterface;
use TheChoice\Contracts\NodeFactoryInterface;
use TheChoice\Contracts\OperatorFactoryInterface;
use TheChoice\NodeType\Context;

class NodeContextFactory implements NodeFactoryInterface
{
    private $_operatorFactory;

    public function __construct(OperatorFactoryInterface $operatorFactory)
    {
        $this->_operatorFactory = $operatorFactory;
    }

    public function build(BuilderInterface $builder, array &$structure): Context
    {
        $node = new Context();

        if (self::nodeHasOperator($structure)) {
            $operatorInstance = $this->_operatorFactory->create(
                $structure['operator'],
                $structure['value']
            );
            $node->setOperator($operatorInstance);
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

        if (self::isNodeStoppable($structure)) {
            $node->setStoppableType(Context::STOP_ALWAYS);
        }

        return $node;
    }

    private static function nodeHasDescription(array &$structure): bool
    {
        return array_key_exists('description', $structure) && \is_string($structure['description']);
    }

    private static function nodeHasPriority(array &$structure): bool
    {
        return array_key_exists('priority', $structure) && (\is_string($structure['priority'] || \is_numeric($structure['priority'])));
    }

    private static function nodeHasParams(array &$structure): bool
    {
        return array_key_exists('params', $structure) && \is_array($structure['params']);
    }

    private static function isNodeStoppable(array &$structure): bool
    {
        return array_key_exists('break', $structure) && \is_string($structure['break']);
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