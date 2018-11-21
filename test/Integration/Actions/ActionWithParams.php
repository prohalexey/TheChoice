<?php

namespace TheChoice\Tests\Integration\Actions;

use \TheChoice\Contracts\ActionContextInterface;

class ActionWithParams implements ActionContextInterface
{
    public $a;
    public $b;

    private $c;

    public function process()
    {
        return
            \is_numeric($this->a) && $this->a === 1 &&
            \is_string($this->b) && $this->b === 'test' &&
            \is_array($this->c) && isset($this->c[0], $this->c[1], $this->c[2]) && $this->c[0] + $this->c[1] + $this->c[2] === 9;
    }

    public function setC($c)
    {
        $this->c = $c;
    }
}