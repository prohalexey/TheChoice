<?php

namespace TheChoice\Contracts;

interface OperatorInterface
{
    public function setValue($value);

    public function getValue();

    public function assert(RuleContextInterface $context): bool;
}