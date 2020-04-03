<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use TheChoice\Context\ContextInterface;

class GreaterThanOrEqual implements OperatorInterface
{
    use GetValueTrait, SetValueTrait;

    public static function getOperatorName(): string
    {
        return 'greaterThanOrEqual';
    }

    public function assert(ContextInterface $context): bool
    {
        return $context->getValue() >= $this->getValue();
    }
}