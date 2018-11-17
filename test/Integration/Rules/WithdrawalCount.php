<?php

namespace TheChoice\Tests\Integration\Rules;

use TheChoice\Contracts\RuleContextInterface;

class WithdrawalCount implements RuleContextInterface
{
    public function getValue()
    {
        return 0;
    }
}