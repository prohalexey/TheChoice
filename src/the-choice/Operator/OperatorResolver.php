<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use TheChoice\Exception\InvalidArgumentException;

class OperatorResolver implements OperatorResolverInterface
{
    public function resolve(string $operatorType)
    {
        $operatorTypeMap = $this->getOperatorMap();

        if (!array_key_exists($operatorType, $operatorTypeMap)) {
            throw new InvalidArgumentException(sprintf('Unknown operator type "%s"', $operatorType));
        }

        return $operatorTypeMap[$operatorType];
    }

    private function getOperatorMap(): array
    {
        return [
            ArrayContain::getOperatorName()         => ArrayContain::class,
            ArrayNotContain::getOperatorName()      => ArrayNotContain::class,
            Equal::getOperatorName()                => Equal::class,
            GreaterThan::getOperatorName()          => GreaterThan::class,
            GreaterThanOrEqual::getOperatorName()   => GreaterThanOrEqual::class,
            LowerThan::getOperatorName()            => LowerThan::class,
            LowerThanOrEqual::getOperatorName()     => LowerThanOrEqual::class,
            NotEqual::getOperatorName()             => NotEqual::class,
            NumericInRange::getOperatorName()       => NumericInRange::class,
            StringContain::getOperatorName()        => StringContain::class,
            StringNotContain::getOperatorName()     => StringNotContain::class,
        ];
    }
}