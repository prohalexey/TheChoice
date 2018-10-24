<?php

namespace TheChoice\Contracts;

interface OperatorFactoryInterface
{
    public function create(string $type, $value): OperatorInterface;
}