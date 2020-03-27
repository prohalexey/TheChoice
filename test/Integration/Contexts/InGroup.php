<?php

namespace TheChoice\Tests\Integration\Contexts;

use TheChoice\Context\ContextInterface;

class InGroup implements ContextInterface
{
    public function getValue()
    {
        return 'testgroup';
    }
}