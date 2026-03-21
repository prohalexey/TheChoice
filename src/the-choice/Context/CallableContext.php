<?php

declare(strict_types=1);

namespace TheChoice\Context;

final readonly class CallableContext implements ContextInterface
{
    /** @var callable */
    private mixed $context;

    public function __construct(callable $context)
    {
        $this->context = $context;
    }

    public function getValue(): mixed
    {
        return ($this->context)();
    }
}
