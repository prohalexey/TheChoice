<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use InvalidArgumentException;
use TheChoice\Node\Collection;
use TheChoice\Node\Node;

class CollectionProcessor extends AbstractProcessor
{
    public function process(Node $node): mixed
    {
        if (!$node instanceof Collection) {
            throw new InvalidArgumentException('Node must be an instance of Collection');
        }

        $result = true;

        $rootNode = $node->getRoot();

        foreach ($node->sort()->all() as $item) {
            $processor = $this->getProcessorByNode($item);
            if (null === $processor) {
                continue;
            }

            $result = $processor->process($item);

            /*
             * If the "Root" node has a result, we should stop here.
             * It does not matter what we return; the result is already set to the "Root" node
             */
            if ($rootNode->hasResult()) {
                return null;
            }

            if (false === $result && Collection::TYPE_AND === $node->getType()) {
                return false;
            }

            if (true === $result && Collection::TYPE_OR === $node->getType()) {
                return true;
            }
        }

        return $result;
    }
}
