<?php

namespace TheChoice;

use TheChoice\Contracts\ContextInterface;

class CallableContext implements ContextInterface
{
    private $rule;

    public function __construct(callable $rule)
    {
        $this->rule = $rule;
    }

    public function getValue()
    {
        return ($this->rule)();
    }
}