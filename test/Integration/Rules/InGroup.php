<?php

use TheChoice\Contracts\RuleContextInterface;

class InGroup implements RuleContextInterface
{
    public function getValue()
    {
        return 'testgroup';
    }
}