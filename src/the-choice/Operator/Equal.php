<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use TheChoice\Context\ContextInterface;

class Equal implements OperatorInterface
{
    use GetValueTrait, SetValueTrait;

    public static function getOperatorName(): string
    {
        return 'equal';
    }

    public function assert(ContextInterface $context): bool
    {
        return $context->getValue() === $this->getValue();
    }
}