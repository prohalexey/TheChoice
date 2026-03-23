<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use InvalidArgumentException;
use TheChoice\Node\Condition;
use TheChoice\Node\Node;

class ConditionProcessor extends AbstractProcessor
{
    public function process(Node $node): mixed
    {
        if (!$node instanceof Condition) {
            throw new InvalidArgumentException('Node must be an instance of Condition');
        }

        $this->traceCollector?->begin('Condition', 'condition');

        $processorIf = $this->getProcessorByNode($node->getIfNode());
        $ifResult = null !== $processorIf ? $processorIf->process($node->getIfNode()) : null;

        if (true === $ifResult) {
            $processorThen = $this->getProcessorByNode($node->getThenNode());
            $result = null !== $processorThen ? $processorThen->process($node->getThenNode()) : false;

            $this->traceCollector?->end($result);

            return $result;
        }

        if (null !== $node->getElseNode()) {
            $processorElse = $this->getProcessorByNode($node->getElseNode());
            $result = null !== $processorElse ? $processorElse->process($node->getElseNode()) : false;

            $this->traceCollector?->end($result);

            return $result;
        }

        $this->traceCollector?->end(false);

        return false;
    }
}
