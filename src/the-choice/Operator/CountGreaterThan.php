<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use Countable;
use TheChoice\Context\ContextInterface;
use TheChoice\Exception\InvalidArgumentException;

class CountGreaterThan extends AbstractOperator
{
    public static function getOperatorName(): string
    {
        return 'countGreaterThan';
    }

    public function setValue(mixed $value): static
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException(
                sprintf('Value passed to CountGreaterThan operator must be numeric, %s given', gettype($value)),
            );
        }

        $this->value = (int)$value;

        return $this;
    }

    public function assert(ContextInterface $context): bool
    {
        $contextValue = $context->getValue();

        if (!is_array($contextValue) && !($contextValue instanceof Countable)) {
            return false;
        }

        return count($contextValue) > $this->value;
    }
}
