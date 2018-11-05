<?php

namespace TheChoice\Operators;

use TheChoice\Contracts\RuleContextInterface;
use TheChoice\Contracts\OperatorInterface;

class Equal implements OperatorInterface
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
        $this->_value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function assert(RuleContextInterface $context): bool
    {
        return $context->getValue() === $this->getValue();
    }
}