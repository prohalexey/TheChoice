<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use TheChoice\Context\ContextInterface;

class NotEqual extends AbstractOperator
{
    public static function getOperatorName(): string
    {
        return 'notEqual';
    }

    public function assert(ContextInterface $context): bool
    {
        return $context->getValue() !== $this->getValue();
    }
}
