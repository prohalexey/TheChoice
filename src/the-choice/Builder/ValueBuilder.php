<?php

declare(strict_types=1);

namespace TheChoice\Builder;

use TheChoice\Node\Value;

final readonly class ValueBuilder implements NodeBuilderInterface
{
    public function __construct(private mixed $value)
    {
    }

    public function build(): Value
    {
        return new Value($this->value);
    }
}
