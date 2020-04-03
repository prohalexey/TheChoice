<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use TheChoice\Context\ContextInterface;

class LowerThan implements OperatorInterface
{
    use GetValueTrait, SetValueTrait;

    public static function getOperatorName(): string
    {
        return 'lowerThan';
    }

    public function assert(ContextInterface $context): bool
    {
        return $context->getValue() < $this->getValue();
    }
}