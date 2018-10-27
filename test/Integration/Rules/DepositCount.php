<?php

use TheChoice\Contracts\ContextInterface;

class DepositCount implements ContextInterface
{
    public function getValue()
    {
        return 1;
    }
}