<?php

namespace TheChoice\Contracts;

use TheChoice\NodeType\Rule;

interface RuleContextFactoryInterface
{
    public function createContextFromRuleNode(Rule $rule): RuleContextInterface;
}