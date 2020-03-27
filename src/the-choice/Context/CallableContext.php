<?php

declare(strict_types=1);

namespace TheChoice\Context;

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