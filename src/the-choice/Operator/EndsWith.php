<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use Override;
use TheChoice\Context\ContextInterface;
use TheChoice\Exception\InvalidArgumentException;

class EndsWith extends AbstractOperator
{
    public static function getOperatorName(): string
    {
        return 'endsWith';
    }

    #[Override]
    public function setValue(mixed $value): static
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(
                sprintf('Value passed to EndsWith operator must be a string, %s given', gettype($value)),
            );
        }

        $this->value = $value;

        return $this;
    }

    public function assert(ContextInterface $context): bool
    {
        $contextValue = $context->getValue();

        if (!is_string($contextValue) || !is_string($this->value)) {
            return false;
        }

        return str_ends_with($contextValue, $this->value);
    }
}
