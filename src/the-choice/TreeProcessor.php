<?php

namespace TheChoice;

use TheChoice\Contracts\ActionContextFactoryInterface;
use TheChoice\Contracts\RuleContextFactoryInterface;
use TheChoice\NodeType\Action;
use TheChoice\NodeType\AndCollection;
use TheChoice\NodeType\Condition;
use TheChoice\NodeType\OrCollection;
use TheChoice\NodeType\Rule;

class TreeProcessor
{
    private $_ruleContextFactory;
    private $_actionContextFactory;

    private $_forcedStopResult;

    private $_processedRules = [];

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

        if ($node instanceof Condition) {
            return $this->processCondition($node);
        }

        if ($node instanceof Action) {
            return $this->processAction($node);
        }

        throw new \InvalidArgumentException(sprintf('Unknown node type "%s"', \gettype($node)));
    }

    private function processAndCollection(AndCollection $node)
    {
        $result = true;

        foreach ($node->sort()->all() as $item) {
            $result = $this->process($item);

            if ($result === false) {
                return false;
            }
        }

        return $result;
    }

    private function processOrCollection(OrCollection $node)
    {
        $result = false;

        foreach ($node->sort()->all() as $item) {
            $result = $this->process($item);

            if ($result === true) {
                return true;
            }
        }

        return $result;
    }

    private function processRule(Rule $node): bool
    {
        $operator = $node->getOperator();
        $operatorValue = $operator->getValue();

        $hash = vsprintf('%s_%s_%s', [
            $node->getRuleType(),
            \get_class($operator),
            \is_array($operatorValue) || \is_object($operatorValue) ? md5(serialize($operatorValue)) : $operatorValue,
        ]);

        if (!isset($this->_processedRules[$hash])) {
            $context = $this->_ruleContextFactory->createContextFromRuleNode($node);
            $this->_processedRules[$hash] = $node->getOperator()->assert($context);
        }

        return $this->_processedRules[$hash];
    }

    private function processCondition(Condition $node)
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