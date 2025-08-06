<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

use TheChoice\Builder\BuilderInterface;
use TheChoice\Node\Context;
use TheChoice\Operator\OperatorInterface;
use TheChoice\Operator\OperatorResolverInterface;

class NodeContextFactory implements NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure): Context
    {
        $node = new Context();

        $node->setRoot($builder->getRoot());

        if (self::nodeHasOperator($structure)) {
            $operatorResolver = $builder->getContainer()->get(OperatorResolverInterface::class);
            if ($operatorResolver instanceof OperatorResolverInterface) {
                $operatorType = $structure['operator'];
                if (is_string($operatorType)) {
                    $operatorType = $operatorResolver->resolve($operatorType);
                    $operator = $builder->getContainer()->get($operatorType);
                    if ($operator instanceof OperatorInterface) {
                        $operator->setValue($structure['value']);
                        $node->setOperator($operator);
                    }
                }
            }
        }

        if (self::nodeHasContextName($structure)) {
            $contextName = $structure['contextName'];
            if (is_string($contextName)) {
                $node->setContextName($contextName);
            }
        }

        if (self::nodeHasDescription($structure)) {
            $description = $structure['description'];
            if (is_string($description)) {
                $node->setDescription($description);
            }
        }

        if (self::nodeHasPriority($structure)) {
            $priority = $structure['priority'];
            if (is_numeric($priority)) {
                $node->setPriority((int)$priority);
            }
        }

        if (self::nodeHasParams($structure)) {
            $params = $structure['params'];
            if (is_array($params)) {
                $node->setParams($params);
            }
        }

        if (self::nodeHasModifiers($structure)) {
            $node->setModifiers($structure['modifiers']);
        }

        if (self::isNodeStoppable($structure)) {
            $node->setStoppableType(Context::STOP_IMMEDIATELY);
        }

        return $node;
    }

    private static function nodeHasDescription(array $structure): bool
    {
        return array_key_exists('description', $structure);
    }

    private static function nodeHasPriority(array $structure): bool
    {
        return array_key_exists('priority', $structure);
    }

    private static function nodeHasParams(array $structure): bool
    {
        return array_key_exists('params', $structure);
    }

    private static function nodeHasModifiers(array $structure): bool
    {
        return array_key_exists('modifiers', $structure);
    }

    private static function isNodeStoppable(array $structure): bool
    {
        return array_key_exists('break', $structure);
    }

    private static function nodeHasOperator(array $structure): bool
    {
        return array_key_exists('operator', $structure) && array_key_exists('value', $structure);
    }

    private static function nodeHasContextName(array $structure): bool
    {
        return array_key_exists('contextName', $structure);
    }
}
