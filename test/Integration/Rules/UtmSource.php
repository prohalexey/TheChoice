<?php

namespace TheChoice\Tests\Integration\Rules;

use TheChoice\Contracts\RuleContextInterface;

class UtmSource implements RuleContextInterface
{
    public function getValue()
    {
        return 'abcd';
    }
}