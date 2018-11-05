<?php

use TheChoice\Contracts\RuleContextInterface;

class VisitCount implements RuleContextInterface
{
    public function getValue()
    {
        return 2;
    }
}