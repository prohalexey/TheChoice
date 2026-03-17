<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use TheChoice\Context\ContextInterface;

class StringNotContain extends AbstractOperator
{
    public static function getOperatorName(): string
    {
        return 'stringNotContain';
    }

    public function assert(ContextInterface $context): bool
    {
        $contextValue = is_scalar($context->getValue()) ? (string)$context->getValue() : '';
        $searchValue = is_scalar($this->getValue()) ? (string)$this->getValue() : '';

        return !str_contains($contextValue, $searchValue);
    }
}
