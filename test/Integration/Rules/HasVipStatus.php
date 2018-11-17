<?php

namespace TheChoice\Tests\Integration\Rules;

use TheChoice\Contracts\RuleContextInterface;

class HasVipStatus implements RuleContextInterface
{
    public function getValue()
    {
        return false;
    }
}