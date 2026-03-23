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

        if (array_key_exists('operator', $structure)) {
            $operatorResolver = $builder->getContainer()->get(OperatorResolverInterface::class);
            if ($operatorResolver instanceof OperatorResolverInterface) {
                $operatorType = $structure['operator'];
                if (is_string($operatorType)) {
                    $operatorType = $operatorResolver->resolve($operatorType);
                    $operator = $builder->getContainer()->get($operatorType);
                    if ($operator instanceof OperatorInterface) {
                        if (array_key_exists('value', $structure)) {
                            $operator->setValue(
                                StorageValueResolver::resolve($structure['value'], $builder),
                            );
                        }

                        $node->setOperator($operator);
                    }
                }
            }
        }

        if (array_key_exists('context', $structure)) {
            $contextName = $structure['context'];
            if (is_string($contextName)) {
                $node->setContextName($contextName);
            }
        }

        if (array_key_exists('description', $structure)) {
            $description = $structure['description'];
            if (is_string($description)) {
                $node->setDescription($description);
            }
        }

        if (array_key_exists('priority', $structure)) {
            $priority = $structure['priority'];
            if (is_numeric($priority)) {
                $node->setPriority((int)$priority);
            }
        }

        if (array_key_exists('params', $structure)) {
            $params = $structure['params'];
            if (is_array($params)) {
                $node->setParams($params);
            }
        }

        if (array_key_exists('modifiers', $structure)) {
            $node->setModifiers($structure['modifiers']);
        }

        if (array_key_exists('break', $structure)) {
            $node->setStoppableType(Context::STOP_IMMEDIATELY);
        }

        return $node;
    }
}
