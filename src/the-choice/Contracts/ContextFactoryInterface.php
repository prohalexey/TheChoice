<?php

namespace TheChoice\Contracts;

use TheChoice\Rule;

interface ContextFactoryInterface
{
    public function createContextFromRule(Rule $rule): ContextInterface;
}