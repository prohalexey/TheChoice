<?php

namespace TheChoice;

use TheChoice\Contracts\ContextFactoryInterface;
use TheChoice\Contracts\ContextInterface;

class ContextFactory implements ContextFactoryInterface
{
    private $_rulesToContextMap = [];

    public function __construct($rules)
    {
        $this->_rulesToContextMap = $rules;
    }

    public function createContextFromRule(Rule $rule): ContextInterface
    {
        $ruleType = $rule->getRuleType();
        if (!array_key_exists($ruleType, $this->_rulesToContextMap)) {
            throw new \InvalidArgumentException(sprintf('Rule type "%s" is not bind', $ruleType));
        }

        $context = $this->_rulesToContextMap[$ruleType];

        if (\is_object($context)) {
            if (!$context instanceof ContextInterface) {
                throw new \InvalidArgumentException(
                    sprintf('Object "%s" not implements ContextInterface', \get_class($context))
                );
            }
            return $context;
        }

        if (\is_callable($context)) {
            return new CallableContext($context);
        }

        if (\is_string($context) && \class_exists($context)) {
            return new $context;
        }

        throw new \InvalidArgumentException(sprintf('Unknown rule type "%s"', $ruleType));
    }
}