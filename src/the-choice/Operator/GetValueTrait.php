<?php

declare(strict_types=1);

namespace TheChoice\Operator;

trait GetValueTrait
{
    protected mixed $value;

    public function getValue(): mixed
    {
        return $this->value;
    }
}
