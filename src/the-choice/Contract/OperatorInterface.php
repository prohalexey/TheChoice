<?php

namespace TheChoice\Contract;

interface OperatorInterface
{
    public function setValue($value);

    public function getValue();

    public function assert(ContextInterface $context): bool;
}