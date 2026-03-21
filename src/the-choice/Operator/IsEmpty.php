<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use TheChoice\Context\ContextInterface;

/**
 * Returns true when the context value is null, an empty string, or an empty array.
 * Does not require a "value" field in the rule definition.
 */
class IsEmpty extends AbstractOperator
{
    public static function getOperatorName(): string
    {
        return 'isEmpty';
    }

    public function assert(ContextInterface $context): bool
    {
        $contextValue = $context->getValue();

        return null === $contextValue || '' === $contextValue || [] === $contextValue;
    }
}
