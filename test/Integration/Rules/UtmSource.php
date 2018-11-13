<?php

use TheChoice\Contracts\RuleContextInterface;

class UtmSource implements RuleContextInterface
{
    public function getValue()
    {
        return 'abcd';
    }
}