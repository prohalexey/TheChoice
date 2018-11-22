<?php

namespace TheChoice\Tests\Integration\Contexts;

use \TheChoice\Contracts\ContextInterface;

class UtmSource implements ContextInterface
{
    public function getValue()
    {
        return 'abcd';
    }
}