<?php

namespace TheChoice\Contracts;

interface OperatorInterface
{
    public function assert(ContextInterface $context): bool;
}