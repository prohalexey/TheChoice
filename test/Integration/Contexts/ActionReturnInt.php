<?php

namespace TheChoice\Tests\Integration\Contexts;

use \TheChoice\Contract\ContextInterface;

class ActionReturnInt implements ContextInterface
{
    public function getValue()
    {
        return 5;
    }
}