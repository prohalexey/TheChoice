<?php

namespace TheChoice\Tests\Integration\Contexts;

use TheChoice\Context\ContextInterface;

class VisitCount implements ContextInterface
{
    public function getValue()
    {
        return 2;
    }
}