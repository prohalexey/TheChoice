<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use Override;
use TheChoice\Context\ContextInterface;
use TheChoice\Exception\InvalidArgumentException;

class IsInstanceOf extends AbstractOperator
{
    public static function getOperatorName(): string
    {
        return 'isInstanceOf';
    }

    #[Override]
    public function setValue(mixed $value): static
    {
        if (!is_string($value) || '' === $value) {
            throw new InvalidArgumentException(
                sprintf(
                    'Value passed to IsInstanceOf operator must be a non-empty class name string, %s given',
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

        if (!is_object($contextValue) || !is_string($this->value)) {
            return false;
        }

        return $contextValue instanceof $this->value;
    }
}
