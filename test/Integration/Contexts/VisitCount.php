<?php

namespace TheChoice\Tests\Integration\Contexts;

use \TheChoice\Contracts\ContextInterface;

class VisitCount implements ContextInterface
{
    public function getValue()
    {
        return 2;
    }
}