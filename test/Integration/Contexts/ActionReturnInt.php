<?php

namespace TheChoice\Tests\Integration\Contexts;

use TheChoice\Context\ContextInterface;

class ActionReturnInt implements ContextInterface
{
    public function getValue()
    {
        return 5;
    }
}