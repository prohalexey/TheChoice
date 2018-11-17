<?php

namespace TheChoice\Tests\Integration\Rules;

use TheChoice\Contracts\RuleContextInterface;

class InGroup implements RuleContextInterface
{
    public function getValue()
    {
        return 'testgroup';
    }
}