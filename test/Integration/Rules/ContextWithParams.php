<?php

namespace TheChoice\Tests\Integration\Rules;

use TheChoice\Contracts\RuleContextInterface;

class ContextWithParams implements RuleContextInterface
{
    public $a;
    public $b;

    private $c;

    public function getValue()
    {
        if (\is_numeric($this->a) && $this->a === 1 &&
            \is_string($this->b) && $this->b === 'test' &&
            \is_array($this->c) && isset($this->c[0], $this->c[1], $this->c[2]) && $this->c[0] + $this->c[1] + $this->c[2] === 9) {

            return 2;
        }

        return 0;
    }

    public function setC($c)
    {
        $this->c = $c;
    }
}