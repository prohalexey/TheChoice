<?php

namespace TheChoice\Tests\Integration\Contexts;

use \TheChoice\Contract\ContextInterface;

class HasVipStatus implements ContextInterface
{
    public function getValue()
    {
        return false;
    }
}