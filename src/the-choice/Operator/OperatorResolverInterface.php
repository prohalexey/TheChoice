<?php

declare(strict_types=1);

namespace TheChoice\Operator;

interface OperatorResolverInterface
{
    /**
     * @param class-string<OperatorInterface> $operatorClass
     */
    public function register(string $operatorType, string $operatorClass): self;

    public function resolve(string $operatorType): string;
}
