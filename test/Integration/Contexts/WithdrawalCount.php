<?php

namespace TheChoice\Tests\Integration\Contexts;

use TheChoice\Context\ContextInterface;

class WithdrawalCount implements ContextInterface
{
    public function getValue()
    {
        return 0;
    }
}