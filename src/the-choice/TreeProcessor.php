<?php

namespace TheChoice;

use TheChoice\Factory\ContextFactory;
use TheChoice\NodeType\AndCollection;
use TheChoice\NodeType\Condition;
use TheChoice\NodeType\OrCollection;
use TheChoice\NodeType\Context;

class TreeProcessor
{
    /** @var ContextFactory */
    private $_contextFactory;
    private $_processedContext = [];

    private $_forcedStopResult;

    public function setContextFactory(ContextFactory $contextFactory)
    {
        $this->_contextFactory = $contextFactory;
        return $this;
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

        if ($node instanceof Context) {
            return $this->processContext($node);
        }

        if ($node instanceof Condition) {
            return $this->processCondition($node);
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

    private function processContext(Context $node): bool
    {
        if (null === $this->_contextFactory) {
            throw new \RuntimeException('Context factory not configured');
        }

        $hash = [
            $node->getContextName(),
        ];

        $operator = $node->getOperator();
        if (null !== $operator) {
            $operatorValue = $operator->getValue();

            $hash[] = \get_class($operator);
            $hash[] = \is_array($operatorValue) || \is_object($operatorValue) ? md5(serialize($operatorValue)) : $operatorValue;
        }

        $params = $node->getParams();
        if (null !== $params) {
            $hash[] = md5(serialize($params));
        }

        $hash = implode('', $hash);

        if (!isset($this->_processedContext[$hash])) {
            $context = $this->_contextFactory->createContextFromContextNode($node);
            if (null !== $operator) {
                $this->_processedContext[$hash] = $operator->assert($context);
            } else {
                $this->_processedContext[$hash] = $context->getValue();
            }

            if ($node->isStoppable()) {
                $this->_forcedStopResult = $this->_processedContext[$hash];
                return $this->_forcedStopResult;
            }
        }

        return $this->_processedContext[$hash];
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
}