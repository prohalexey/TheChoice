<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use TheChoice\Context\ContextInterface;
use TheChoice\Exception\InvalidArgumentException;

class ContainsKey extends AbstractOperator
{
    public static function getOperatorName(): string
    {
        return 'containsKey';
    }

    public function setValue(mixed $value): static
    {
        if (!is_string($value) && !is_int($value)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Value passed to ContainsKey operator must be a string or int key, %s given',
                    gettype($value),
                ),
            );
        }

        $this->value = $value;

        return $this;
    }

    public function assert(ContextInterface $context): bool
    {
        $contextValue = $context->getValue();

        if (!is_array($contextValue)) {
            return false;
        }

        return array_key_exists($this->value, $contextValue);
    }
}
