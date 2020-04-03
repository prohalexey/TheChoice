<?php

namespace TheChoice\Tests\Integration\Contexts;

use TheChoice\Context\ContextInterface;

class ActionWithParams implements ContextInterface
{
    public $a;
    public $b;

    private $c;

    public function getValue()
    {
        return
            is_numeric($this->a) && $this->a === 1 &&
            is_string($this->b) && $this->b === 'test' &&
            is_array($this->c) && isset($this->c[0], $this->c[1], $this->c[2]) && $this->c[0] + $this->c[1] + $this->c[2] === 9;
    }

    public function setC($c)
    {
        $this->c = $c;
    }
}