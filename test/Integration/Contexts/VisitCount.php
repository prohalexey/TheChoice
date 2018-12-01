<?php

namespace TheChoice\Tests\Integration\Contexts;

use \TheChoice\Contract\ContextInterface;

class VisitCount implements ContextInterface
{
    public function getValue()
    {
        return 2;
    }
}