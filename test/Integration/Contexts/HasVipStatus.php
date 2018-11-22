<?php

namespace TheChoice\Tests\Integration\Contexts;

use \TheChoice\Contracts\ContextInterface;

class HasVipStatus implements ContextInterface
{
    public function getValue()
    {
        return false;
    }
}