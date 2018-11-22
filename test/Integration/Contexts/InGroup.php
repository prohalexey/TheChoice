<?php

namespace TheChoice\Tests\Integration\Contexts;

use \TheChoice\Contracts\ContextInterface;

class InGroup implements ContextInterface
{
    public function getValue()
    {
        return 'testgroup';
    }
}