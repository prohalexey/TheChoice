<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use TheChoice\Context\ContextInterface;

class StringContain implements OperatorInterface
{
    use GetValueTrait;
    use SetValueTrait;

    public static function getOperatorName(): string
    {
        return 'stringContain';
    }

    public function assert(ContextInterface $context): bool
    {
        $contextValue = $context->getValue();
        $searchValue = $this->getValue();

        if (!is_string($contextValue) || !is_string($searchValue)) {
            return false;
        }

        return str_contains($contextValue, $searchValue);
    }
}
