<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use TheChoice\Node\Condition;

class ConditionProcessor extends AbstractProcessor
{
    public function process(Condition $node)
    {
        $processorIf = $this->getProcessorByNode($node->getIfNode());
        if ($processorIf->process($node->getIfNode())) {
            $processorThen = $this->getProcessorByNode($node->getThenNode());
            return $processorThen->process($node->getThenNode());
        }

        if (null !== $node->getElseNode()) {
            $processorElse = $this->getProcessorByNode($node->getElseNode());
            return $processorElse->process($node->getElseNode());
        }

        return false;
    }
}