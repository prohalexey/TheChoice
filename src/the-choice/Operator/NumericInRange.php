<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use TheChoice\Context\ContextInterface;
use TheChoice\Exception\InvalidArgumentException;

class NumericInRange implements OperatorInterface
{
    use GetValueTrait;

    public static function getOperatorName(): string
    {
        return 'numericInRange';
    }

    public function setValue($value): self
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException(
                sprintf('Value passed to NumericInRange operator is not an array, %s given', gettype($value))
            );
        }

        $argsCount = count($value);
        if ($argsCount !== 2) {
            throw new InvalidArgumentException(
                sprintf('NumericInRange operator accept exact 2 args. %d given', $argsCount)
            );
        }

        $this->value = $value;

        return $this;
    }

    public function assert(ContextInterface $context): bool
    {
        $contextValue = $context->getValue();

        list ($leftBoundary, $rightBoundary) = $this->getValue();

        if ($leftBoundary > $rightBoundary) {
            return $contextValue >= $rightBoundary && $contextValue <= $leftBoundary;
        } else {
            return $contextValue >= $leftBoundary && $contextValue <= $rightBoundary;
        }
    }
}