<?php

namespace TheChoice\Tests\Integration\Contexts;

use TheChoice\Context\ContextInterface;

class Action1 implements ContextInterface
{
    public function getValue()
    {
        return true;
    }
}