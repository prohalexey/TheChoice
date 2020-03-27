<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use TheChoice\Context\ContextInterface;

class StringNotContain implements OperatorInterface
{
    use GetValueTrait, SetValueTrait;

    public static function getOperatorName(): string
    {
        return 'stringNotContain';
    }

    public function assert(ContextInterface $context): bool
    {
        return mb_strpos((string)$context->getValue(), (string)$this->getValue()) === false;
    }
}