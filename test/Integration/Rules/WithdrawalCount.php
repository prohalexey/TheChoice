<?php

use TheChoice\Contracts\RuleContextInterface;

class WithdrawalCount implements RuleContextInterface
{
    public function getValue()
    {
        return 0;
    }
}