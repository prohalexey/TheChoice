<?php

namespace TheChoice\Builder;

use TheChoice\Factory\NodeConditionFactory;
use TheChoice\Factory\NodeCollectionFactory;
use TheChoice\Factory\NodeContextFactory;

use TheChoice\Contracts\OperatorFactoryInterface;
use TheChoice\Contracts\BuilderInterface;

class ArrayBuilder implements BuilderInterface
{
    private $_nodeConditionFactory;
    private $_nodeCollectionFactory;
    private $_nodeContextFactory;

    public function __construct(OperatorFactoryInterface $operatorFactory)
    {
        $this->_nodeConditionFactory = new NodeConditionFactory();
        $this->_nodeCollectionFactory = new NodeCollectionFactory();
        $this->_nodeContextFactory = new NodeContextFactory($operatorFactory);
    }

    public function build(&$structure)
    {
        if (!array_key_exists('node', $structure)) {
            throw new \LogicException('The "node" property is absent!');
        }

        if ($structure['node'] === 'condition') {
            return $this->_nodeConditionFactory->build($this, $structure);
        }

        if ($structure['node'] === 'collection') {
            return $this->_nodeCollectionFactory->build($this, $structure);
        }

        if ($structure['node'] === 'context') {
            return $this->_nodeContextFactory->build($this, $structure);
        }

        throw new \LogicException('Unknown node type');
    }
}
