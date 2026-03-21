<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use TheChoice\Context\ContextInterface;

/**
 * Returns true when the context value is strictly null.
 * Does not require a "value" field in the rule definition.
 */
class IsNull extends AbstractOperator
{
    public static function getOperatorName(): string
    {
        return 'isNull';
    }

    public function assert(ContextInterface $context): bool
    {
        return null === $context->getValue();
    }
}
