<?php

use \TheChoice\Contracts\ActionContextInterface;

class Action2 implements ActionContextInterface
{
    public function process()
    {
        return false;
    }
}