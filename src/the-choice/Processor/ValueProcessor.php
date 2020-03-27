<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use TheChoice\Node\Value;

class ValueProcessor extends AbstractProcessor
{
    public function process(Value $node)
    {
        return $node->getValue();
    }
}