<?php

use \TheChoice\Contracts\ActionContextInterface;

class Action1 implements ActionContextInterface
{
    public function process()
    {
        return true;
    }
}