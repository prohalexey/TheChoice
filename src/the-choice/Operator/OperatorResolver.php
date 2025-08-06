<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use TheChoice\Exception\InvalidArgumentException;

class OperatorResolver implements OperatorResolverInterface
{
    /**
     * @return class-string
     */
    public function resolve(string $operatorType): string
    {
        return match ($operatorType) {
            ArrayContain::getOperatorName()       => ArrayContain::class,
            ArrayNotContain::getOperatorName()    => ArrayNotContain::class,
            Equal::getOperatorName()              => Equal::class,
            GreaterThan::getOperatorName()        => GreaterThan::class,
            GreaterThanOrEqual::getOperatorName() => GreaterThanOrEqual::class,
            LowerThan::getOperatorName()          => LowerThan::class,
            LowerThanOrEqual::getOperatorName()   => LowerThanOrEqual::class,
            NotEqual::getOperatorName()           => NotEqual::class,
            NumericInRange::getOperatorName()     => NumericInRange::class,
            StringContain::getOperatorName()      => StringContain::class,
            StringNotContain::getOperatorName()   => StringNotContain::class,

            default => throw new InvalidArgumentException(
                sprintf('Operator "%s" is not supported.', $operatorType),
            )
        };
    }
}
