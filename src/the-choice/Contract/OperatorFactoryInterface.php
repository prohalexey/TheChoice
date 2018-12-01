<?php

namespace TheChoice\Contract;

interface OperatorFactoryInterface
{
    public function create(string $type, $value): OperatorInterface;
}