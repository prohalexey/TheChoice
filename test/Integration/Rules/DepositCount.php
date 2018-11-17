<?php

namespace TheChoice\Tests\Integration\Rules;

use TheChoice\Contracts\RuleContextInterface;

class DepositCount implements RuleContextInterface
{
    public function getValue()
    {
        return 2;
    }
}