<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use TheChoice\Context\ContextInterface;

class StringContain implements OperatorInterface
{
    use GetValueTrait, SetValueTrait;

    public static function getOperatorName(): string
    {
        return 'stringContain';
    }

    public function assert(ContextInterface $context): bool
    {
        return mb_strpos((string)$context->getValue(), (string)$this->getValue()) !== false;
    }
}