<?php

declare(strict_types=1);

namespace TheChoice\Operator;

abstract class AbstractOperator implements OperatorInterface
{
    protected mixed $value = null;

    public function setValue(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
