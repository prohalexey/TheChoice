<?php

declare(strict_types=1);

namespace TheChoice\Operator;

interface OperatorResolverInterface
{
    public function resolve(string $operatorType);
}