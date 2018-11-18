<?php

namespace TheChoice\Operators;

use TheChoice\Contracts\RuleContextInterface;
use TheChoice\Contracts\OperatorInterface;

class NumericInRange implements OperatorInterface
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
                sprintf('Value passed to NumericInRange is not an array, %s given', \gettype($value))
            );
        }

        $argsCount = \count($value);
        if ($argsCount !== 2) {
            throw new \InvalidArgumentException(
                sprintf('NumericInRange rule accept exact 2 args. %d given', $argsCount)
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
        $contextValue = $context->getValue();

        list ($leftBoundary, $rightBoundary) = $this->getValue();

        if ($leftBoundary > $rightBoundary) {
            $tmp = $leftBoundary;
            $leftBoundary = $rightBoundary;
            $rightBoundary = $tmp;
        }

        return $contextValue >= $leftBoundary && $contextValue <= $rightBoundary;
    }
}