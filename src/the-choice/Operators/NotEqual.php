<?php

namespace TheChoice\Operators;

use TheChoice\Contracts\ContextInterface;
use TheChoice\Contracts\OperatorInterface;

class NotEqual implements OperatorInterface
{
    private $_value;

    public function __construct($value)
    {
        $this->_value = $value;
    }

    public function assert(ContextInterface $context): bool
    {
        return $context->getValue() !== $this->_value;
    }
}