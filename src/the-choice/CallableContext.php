<?php

namespace TheChoice;

use TheChoice\Contract\ContextInterface;

final class CallableContext implements ContextInterface
{
    private $context;

    public function __construct(callable $context)
    {
        $this->context = $context;
    }

    public function getValue()
    {
        return ($this->context)();
    }
}