<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use InvalidArgumentException;
use TheChoice\Node\Node;
use TheChoice\Node\Value;

class ValueProcessor extends AbstractProcessor
{
    public function process(Node $node): mixed
    {
        if (!$node instanceof Value) {
            throw new InvalidArgumentException('Node must be an instance of Value');
        }

        $result = $node->getValue();

        $this->traceCollector?->begin('Value', 'value');
        $this->traceCollector?->end($result);

        return $result;
    }
}
