<?php

namespace TheChoice\Tests\Integration\Contexts;

use \TheChoice\Contract\ContextInterface;

class DepositCount implements ContextInterface
{
    public function getValue()
    {
        return 2;
    }
}