<?php

namespace TheChoice;

use TheChoice\Contracts\ActionContextInterface;

final class CallableActionContext implements ActionContextInterface
{
    private $action;

    public function __construct(callable $action)
    {
        $this->action = $action;
    }

    public function process()
    {
        return ($this->action)();
    }
}