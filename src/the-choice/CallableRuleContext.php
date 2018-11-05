<?php

namespace TheChoice;

use TheChoice\Contracts\RuleContextInterface;

final class CallableRuleContext implements RuleContextInterface
{
    private $rule;

    public function __construct(callable $rule)
    {
        $this->rule = $rule;
    }

    public function getValue()
    {
        return ($this->rule)();
    }
}