<?php

namespace TheChoice;

use ChrisKonnertz\StringCalc\StringCalc;
use TheChoice\Factory\ContextFactory;

use TheChoice\Node\Collection;
use TheChoice\Node\Condition;
use TheChoice\Node\Context;
use TheChoice\Node\Tree;
use TheChoice\Node\Value;

class TreeProcessor
{
    private $stringCalc;

    /** @var ContextFactory */
    private $_contextFactory;
    private $_processedContext = [];

    private $_forcedStopResult;

    public function __construct()
    {
        $this->stringCalc = new StringCalc();
    }

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

        if ($node instanceof Tree) {
            return $this->processTree($node);
        }

        if ($node instanceof Collection) {
            return $this->processCollection($node);
        }

        if ($node instanceof Context) {
            return $this->processContext($node);
        }

        if ($node instanceof Condition) {
            return $this->processCondition($node);
        }

        if ($node instanceof Value) {
            return $this->processValue($node);
        }

        throw new \InvalidArgumentException(sprintf('Unknown node type "%s"', \gettype($node)));
    }

    private function processTree(Tree $node)
    {
        return $this->process($node->getNodes());
    }

    private function processCollection(Collection $node)
    {
        $result = true;

        foreach ($node->sort()->all() as $item) {
            $result = $this->process($item);

            if ($result === false && $node->getType() === Collection::TYPE_AND) {
                return false;
            }

            if ($result === true && $node->getType() === Collection::TYPE_OR) {
                return true;
            }
        }

        return $result;
    }

    private function processContext(Context $node)
    {
        if (null === $this->_contextFactory) {
            throw new \RuntimeException('Context factory not configured');
        }

        $hash = [
            $node->getContextName(),
        ];

        $params = $node->getParams();
        if (\count($params) > 0) {
            $hash[] = md5(serialize($params));
        }

        $operator = $node->getOperator();
        if (null !== $operator) {
            $operatorValue = $operator->getValue();

            $hash[] = \get_class($operator);
            $hash[] = \is_array($operatorValue) || \is_object($operatorValue) ? md5(serialize($operatorValue)) : $operatorValue;
        }

        $modifiers = $node->getModifiers();
        if (\count($modifiers) > 0) {
            $hash[] = md5(serialize($modifiers));
        }

        $hash = implode('', $hash);

        if (!isset($this->_processedContext[$hash])) {
            $context = $this->_contextFactory->createContextFromContextNode($node);

            if (null !== $operator) {
                if (\count($modifiers) > 0) {
                    $context = new CallableContext(function () use ($context, $node) {
                        return $this->processContextModifiers($context->getValue(), $node);
                    });
                }

                $this->_processedContext[$hash] = $operator->assert($context);
            } else {
                $this->_processedContext[$hash] = $this->processContextModifiers($context->getValue(), $node);
            }
        }

        if ($node->isStoppable()) {
            $this->_forcedStopResult = $this->_processedContext[$hash];
        }

        return $this->_processedContext[$hash];
    }

    private function processContextModifiers($value, Context $node)
    {
        $tree = $node->getTree();

        $vars = ['$context' => $value];
        if (null !== $tree) {
            $storage = $tree->getStorage();
            $vars = array_merge($vars, $storage);
        }

        foreach ($node->getModifiers() as $modifier) {
            $modifier = str_replace(array_keys($vars), array_values($vars), $modifier);
            $value = $this->stringCalc->calculate($modifier);
            $vars['$context'] = $value;
        }

        return $value;
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

    private function processValue(Value $node)
    {
        return $node->getValue();
    }
}