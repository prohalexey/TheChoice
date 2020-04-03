<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use TheChoice\Node\Collection;

class CollectionProcessor extends AbstractProcessor
{
    public function process(Collection $node)
    {
        $result = true;

        $rootNode = $node->getRoot();

        foreach ($node->sort()->all() as $item) {
            $processor = $this->getProcessorByNode($item);
            $result = $processor->process($item);

            /**
             * If the "Root" node has a result, we should stop here.
             * It does not matter what we return, the result is already set to the "Root" node
             */
            if ($rootNode->hasResult()) {
                return;
            }

            if ($result === false && $node->getType() === Collection::TYPE_AND) {
                return false;
            }

            if ($result === true && $node->getType() === Collection::TYPE_OR) {
                return true;
            }
        }

        return $result;
    }
}