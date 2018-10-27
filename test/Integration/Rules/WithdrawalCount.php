<?php

use TheChoice\Contracts\ContextInterface;

class WithdrawalCount implements ContextInterface
{
    public function getValue()
    {
        return 0;
    }
}