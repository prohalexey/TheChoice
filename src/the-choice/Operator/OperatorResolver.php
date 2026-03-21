<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use TheChoice\Exception\InvalidArgumentException;

class OperatorResolver implements OperatorResolverInterface
{
    /** @var array<string, class-string<OperatorInterface>> */
    private array $operatorMap = [];

    /**
     * @param array<string, class-string<OperatorInterface>> $operatorMap
     */
    public function __construct(array $operatorMap = [])
    {
        foreach (self::getDefaultMap() as $operatorType => $operatorClass) {
            $this->register($operatorType, $operatorClass);
        }

        foreach ($operatorMap as $operatorType => $operatorClass) {
            $this->register($operatorType, $operatorClass);
        }
    }

    /**
     * @param class-string<OperatorInterface> $operatorClass
     */
    public function register(string $operatorType, string $operatorClass): self
    {
        if (!is_a($operatorClass, OperatorInterface::class, true)) {
            throw new InvalidArgumentException(
                sprintf('Operator class "%s" must implement %s', $operatorClass, OperatorInterface::class),
            );
        }

        $this->operatorMap[$operatorType] = $operatorClass;

        return $this;
    }

    /**
     * @return class-string<OperatorInterface>
     */
    public function resolve(string $operatorType): string
    {
        if (!array_key_exists($operatorType, $this->operatorMap)) {
            throw new InvalidArgumentException(
                sprintf('Operator "%s" is not supported.', $operatorType),
            );
        }

        return $this->operatorMap[$operatorType];
    }

    /**
     * @return array<string, class-string<OperatorInterface>>
     */
    private static function getDefaultMap(): array
    {
        return [
            ArrayContain::getOperatorName()       => ArrayContain::class,
            ArrayNotContain::getOperatorName()    => ArrayNotContain::class,
            ContainsKey::getOperatorName()        => ContainsKey::class,
            CountEqual::getOperatorName()         => CountEqual::class,
            CountGreaterThan::getOperatorName()   => CountGreaterThan::class,
            EndsWith::getOperatorName()           => EndsWith::class,
            Equal::getOperatorName()              => Equal::class,
            GreaterThan::getOperatorName()        => GreaterThan::class,
            GreaterThanOrEqual::getOperatorName() => GreaterThanOrEqual::class,
            IsEmpty::getOperatorName()            => IsEmpty::class,
            IsInstanceOf::getOperatorName()       => IsInstanceOf::class,
            IsNull::getOperatorName()             => IsNull::class,
            LowerThan::getOperatorName()          => LowerThan::class,
            LowerThanOrEqual::getOperatorName()   => LowerThanOrEqual::class,
            MatchesRegex::getOperatorName()       => MatchesRegex::class,
            NotEqual::getOperatorName()           => NotEqual::class,
            NumericInRange::getOperatorName()     => NumericInRange::class,
            StartsWith::getOperatorName()         => StartsWith::class,
            StringContain::getOperatorName()      => StringContain::class,
            StringNotContain::getOperatorName()   => StringNotContain::class,
        ];
    }
}
