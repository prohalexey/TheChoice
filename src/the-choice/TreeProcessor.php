<?php

namespace TheChoice;

use TheChoice\Contracts\ActionContextFactoryInterface;
use TheChoice\Contracts\RuleContextFactoryInterface;
use TheChoice\NodeType\Action;
use TheChoice\NodeType\AndCollection;
use TheChoice\NodeType\Assert;
use TheChoice\NodeType\OrCollection;
use TheChoice\NodeType\Rule;

class TreeProcessor
{
    private $_ruleContextFactory;
    private $_actionContextFactory;

    private $_forcedStopResult;

    public function __construct(
        RuleContextFactoryInterface $ruleContextFactory,
        ActionContextFactoryInterface $actionContextFactory
    )
    {
        $this->_ruleContextFactory = $ruleContextFactory;
        $this->_actionContextFactory = $actionContextFactory;
    }

    public function process($node)
    {
        if (null !== $this->_forcedStopResult) {
            return $this->_forcedStopResult;
        }

        if ($node instanceof AndCollection) {
            return $this->processAndCollection($node);
        }

        if ($node instanceof OrCollection) {
            return $this->processOrCollection($node);
        }

        if ($node instanceof Rule) {
            return $this->processRule($node);
        }

        if ($node instanceof Assert) {
            return $this->processAssert($node);
        }

        if ($node instanceof Action) {
            return $this->processAction($node);
        }

        throw new \InvalidArgumentException(sprintf('Unknown node type "%s"', \gettype($node)));
    }

    private function processAndCollection(AndCollection $node): bool
    {
        foreach ($node->all() as $item) {
            $result = $this->process($item);

            if ($result === false) {
                return false;
            }
        }
        return true;
    }

    private function processOrCollection(OrCollection $node): bool
    {
        foreach ($node->all() as $item) {
            $result = $this->process($item);

            if ($result === true) {
                return true;
            }
        }

        return false;
    }

    private function processRule(Rule $node): bool
    {
        $context = $this->_ruleContextFactory->createContextFromRuleNode($node);

        return $node->getOperator()->assert($context);
    }

    private function processAssert(Assert $node)
    {
        if ($this->process($node->getIf())) {
            return $this->process($node->getThen());
        }

        $else = $node->getElse();
        if (null !== $else) {
            return $this->process($else);
        }

        return false;
    }

    private function processAction(Action $node)
    {
        $action = $this->_actionContextFactory->createContextFromActionNode($node);

        if ($node->isStoppable()){
            $this->_forcedStopResult = $action->process();
            return $this->_forcedStopResult;
        }

        return $action->process();
    }
}