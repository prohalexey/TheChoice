<?php

namespace TheChoice\Operators;

use TheChoice\Contracts\RuleContextInterface;
use TheChoice\Contracts\OperatorInterface;

class ArrayContain implements OperatorInterface
{
    private $_value;

    public function __construct($value = null)
    {
        if (null !== $value) {
            $this->setValue($value);
        }
    }

    public function setValue($value)
    {
        if (!\is_array($value)) {
            throw new \InvalidArgumentException(
                sprintf('Value passed to ArrayContain is not an array, %s given', \gettype($value))
            );
        }

        $this->_value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function assert(RuleContextInterface $context): bool
    {
        return \in_array($context->getValue(), $this->getValue(), true);
    }
}