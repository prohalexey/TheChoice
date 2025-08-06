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

        $processorIf = $this->getProcessorByNode($node->getIfNode());
        if (null !== $processorIf && $processorIf->process($node->getIfNode())) {
            $processorThen = $this->getProcessorByNode($node->getThenNode());
            if (null !== $processorThen) {
                return $processorThen->process($node->getThenNode());
            }
        }

        if (null !== $node->getElseNode()) {
            $processorElse = $this->getProcessorByNode($node->getElseNode());
            if (null !== $processorElse) {
                return $processorElse->process($node->getElseNode());
            }
        }

        return false;
    }
}
