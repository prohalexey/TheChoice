<?php

namespace TheChoice\Tests\Integration\Contexts;

use \TheChoice\Contracts\ContextInterface;

class ActionBreak implements ContextInterface
{
    public function getValue()
    {
        return 5;
    }
}