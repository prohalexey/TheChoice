<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use Override;
use TheChoice\Context\ContextInterface;
use TheChoice\Exception\InvalidArgumentException;

class MatchesRegex extends AbstractOperator
{
    public static function getOperatorName(): string
    {
        return 'matchesRegex';
    }

    #[Override]
    public function setValue(mixed $value): static
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(
                sprintf('Value passed to MatchesRegex operator must be a string pattern, %s given', gettype($value)),
            );
        }

        if (false === @preg_match($value, '')) {
            throw new InvalidArgumentException(
                sprintf('Value passed to MatchesRegex operator is not a valid regex pattern: "%s"', $value),
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

        return (bool)preg_match($this->value, $contextValue);
    }
}
