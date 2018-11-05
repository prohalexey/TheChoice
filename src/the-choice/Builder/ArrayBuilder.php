<?php

namespace TheChoice\Builder;

use TheChoice\Factory\NodeActionFactory;
use TheChoice\Factory\NodeAssertFactory;
use TheChoice\Factory\NodeCollectionFactory;
use TheChoice\Factory\NodeRuleFactory;

use TheChoice\Contracts\OperatorFactoryInterface;
use TheChoice\Contracts\BuilderInterface;

class ArrayBuilder implements BuilderInterface
{
    private $_nodeActionFactory;
    private $_nodeAssertFactory;
    private $_nodeCollectionFactory;
    private $_nodeRuleFactory;

    public function __construct(OperatorFactoryInterface $operatorFactory)
    {
        $this->_nodeActionFactory = new NodeActionFactory();
        $this->_nodeAssertFactory = new NodeAssertFactory();
        $this->_nodeCollectionFactory = new NodeCollectionFactory();
        $this->_nodeRuleFactory = new NodeRuleFactory($operatorFactory);
    }

    public function build(&$structure)
    {
        if (!array_key_exists('node', $structure)) {
            throw new \LogicException('The "node" property is absent!');
        }

        if ($structure['node'] === 'action') {
            return $this->_nodeActionFactory->build($this, $structure);
        }

        if ($structure['node'] === 'assert') {
            return $this->_nodeAssertFactory->build($this, $structure);
        }

        if ($structure['node'] === 'collection') {
            return $this->_nodeCollectionFactory->build($this, $structure);
        }

        if ($structure['node'] === 'rule') {
            return $this->_nodeRuleFactory->build($this, $structure);
        }

        throw new \LogicException('Unknown node type');
    }
}
