<?php

namespace TheChoice\Tests\Integration\Actions;

use \TheChoice\Contracts\ActionContextInterface;

class ActionBreak implements ActionContextInterface
{
    public function process()
    {
        return 5;
    }
}