<?php

declare(strict_types=1);

namespace TheChoice\Operator;

trait SetValueTrait
{
    protected mixed $value;

    public function setValue(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }
}
