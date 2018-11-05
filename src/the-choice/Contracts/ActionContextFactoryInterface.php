<?php

namespace TheChoice\Contracts;

use TheChoice\NodeType\Action;

interface ActionContextFactoryInterface
{
    public function createContextFromActionNode(Action $action): ActionContextInterface;
}