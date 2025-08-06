<?php

namespace TheChoice\Tests\Integration\Contexts;

use TheChoice\Context\ContextInterface;

class ContextWithParams implements ContextInterface
{
    public $a;

    public $b;

    private $c;

    public function getValue(): int
    {
        if (is_numeric($this->a) && 1 === $this->a
            && is_string($this->b) && 'test' === $this->b
            && is_array($this->c) && isset($this->c[0], $this->c[1], $this->c[2]) && $this->c[0] + $this->c[1] + $this->c[2] === 9) {
            return 2;
        }

        return 0;
    }

    public function setC($c): void
    {
        $this->c = $c;
    }
}
