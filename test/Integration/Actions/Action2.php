<?php

namespace TheChoice\Tests\Integration\Actions;

use \TheChoice\Contracts\ActionContextInterface;

class Action2 implements ActionContextInterface
{
    public function process()
    {
        return false;
    }
}