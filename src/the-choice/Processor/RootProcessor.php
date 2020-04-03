<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use TheChoice\Node\Root;

class RootProcessor extends AbstractProcessor
{
    public function process(Root $node)
    {
        $rules = $node->getRules();

        $processor = $this->getProcessorByNode($rules);

        $result = $processor->process($rules);
        if ($node->hasResult()) {
            return $node->getResult();
        }

        return $result;
    }
}