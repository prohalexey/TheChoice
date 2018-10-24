<?php

namespace TheChoice\Operators;

use TheChoice\Contracts\ContextInterface;
use TheChoice\Contracts\OperatorInterface;

class ArrayNotContain implements OperatorInterface
{
    private $_arrayValue;

    public function __construct($arrayValue)
    {
        if (!is_array($arrayValue)) {
            throw new \InvalidArgumentException(
                sprintf('Value passed to ArrayContain is not an array, %s given', \gettype($arrayValue))
            );
        }
        $this->_arrayValue = $arrayValue;
    }

    public function assert(ContextInterface $context): bool
    {
        return !\in_array($context->getValue(), $this->_arrayValue, true);
    }
}