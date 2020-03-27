<?php

declare(strict_types=1);

namespace TheChoice\Operator;

trait GetValueTrait
{
    protected $value;

    public function getValue()
    {
        return $this->value;
    }
}