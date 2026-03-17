<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use TheChoice\Context\ContextInterface;
use TheChoice\Exception\InvalidArgumentException;

class NumericInRange extends AbstractOperator
{
    public static function getOperatorName(): string
    {
        return 'numericInRange';
    }

    public function setValue(mixed $value): static
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException(
                sprintf('Value passed to NumericInRange operator is not an array, %s given', gettype($value)),
            );
        }

        $argsCount = count($value);
        if (2 !== $argsCount) {
            throw new InvalidArgumentException(
                sprintf('NumericInRange operator accept exact 2 args. %d given', $argsCount),
            );
        }

        $this->value = $value;

        return $this;
    }

    public function assert(ContextInterface $context): bool
    {
        $contextValue = $context->getValue();

        $value = $this->getValue();
        if (!is_array($value) || 2 !== count($value)) {
            return false;
        }

        [$leftBoundary, $rightBoundary] = $value;

        if ($leftBoundary > $rightBoundary) {
            return $contextValue >= $rightBoundary && $contextValue <= $leftBoundary;
        }

        return $contextValue >= $leftBoundary && $contextValue <= $rightBoundary;
    }
}
