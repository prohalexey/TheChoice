<?php

namespace TheChoice\Tests\Integration\Contexts;

use TheChoice\Context\ContextInterface;

class UtmSource implements ContextInterface
{
    public function getValue()
    {
        return 'abcd';
    }
}