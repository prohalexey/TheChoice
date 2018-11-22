<?php

namespace TheChoice\Tests\Integration\Contexts;

use \TheChoice\Contracts\ContextInterface;

class Action1 implements ContextInterface
{
    public function getValue()
    {
        return true;
    }
}