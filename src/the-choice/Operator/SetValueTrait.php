<?php

declare(strict_types=1);

namespace TheChoice\Operator;

trait SetValueTrait
{
    protected $value;

    public function setValue($value): self
    {
        $this->value = $value;
        return $this;
    }
}