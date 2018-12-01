<?php

namespace TheChoice\Tests\Integration\Contexts;

use \TheChoice\Contract\ContextInterface;

class Action2 implements ContextInterface
{
    public function getValue()
    {
        return false;
    }
}