<?php

declare(strict_types=1);

namespace TheChoice\Context;

use TheChoice\Node\Context;

interface ContextFactoryInterface
{
    public function createContextFromContextNode(Context $node): ContextInterface;
}