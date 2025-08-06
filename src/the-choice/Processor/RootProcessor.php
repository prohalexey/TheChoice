<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use InvalidArgumentException;
use TheChoice\Node\Node;
use TheChoice\Node\Root;

class RootProcessor extends AbstractProcessor
{
    public function process(Node $node): mixed
    {
        if (!$node instanceof Root) {
            throw new InvalidArgumentException('Node must be an instance of Root');
        }

        $rules = $node->getRules();

        $processor = $this->getProcessorByNode($rules);
        if (null === $processor) {
            return null;
        }

        $result = $processor->process($rules);
        if ($node->hasResult()) {
            return $node->getResult();
        }

        return $result;
    }
}
