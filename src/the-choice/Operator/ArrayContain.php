<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use TheChoice\Context\ContextInterface;
use TheChoice\Exception\InvalidArgumentException;

class ArrayContain extends AbstractOperator
{
    public static function getOperatorName(): string
    {
        return 'arrayContain';
    }

    public function setValue(mixed $value): static
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException(
                sprintf('Value passed to ArrayContain is not an array, %s given', gettype($value)),
            );
        }

        $this->value = $value;

        return $this;
    }

    public function assert(ContextInterface $context): bool
    {
        return in_array($context->getValue(), $this->getValue(), true);
    }

    public function getValue(): array // @phpstan-ignore-line
    {
        return (array)$this->value;
    }
}
