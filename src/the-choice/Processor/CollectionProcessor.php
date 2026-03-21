<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use InvalidArgumentException;
use TheChoice\Exception\LogicException;
use TheChoice\Node\Collection;
use TheChoice\Node\Node;

class CollectionProcessor extends AbstractProcessor
{
    public function process(Node $node): mixed
    {
        if (!$node instanceof Collection) {
            throw new InvalidArgumentException('Node must be an instance of Collection');
        }

        $this->traceCollector?->begin('Collection', $node->getType());

        $result = match ($node->getType()) {
            Collection::TYPE_AND      => $this->processShortCircuit($node, false),
            Collection::TYPE_OR       => $this->processShortCircuit($node, true),
            Collection::TYPE_NOT      => $this->processNot($node),
            Collection::TYPE_AT_LEAST => $this->processAtLeast($node),
            Collection::TYPE_EXACTLY  => $this->processExactly($node),
            default                   => throw new InvalidArgumentException(
                sprintf('Unsupported collection type "%s"', $node->getType()),
            ),
        };

        $this->traceCollector?->end($result);

        return $result;
    }

    /**
     * AND semantics: short-circuits on the first false result.
     * OR semantics:  short-circuits on the first true result.
     */
    private function processShortCircuit(Collection $node, bool $shortCircuitValue): mixed
    {
        $result = true;
        $rootNode = $node->getRoot();

        foreach ($node->sorted() as $item) {
            $processor = $this->getProcessorByNode($item);
            if (null === $processor) {
                continue;
            }

            $result = $processor->process($item);

            if ($rootNode->hasResult()) {
                return null;
            }

            if ($result === $shortCircuitValue) {
                return $shortCircuitValue;
            }
        }

        return $result;
    }

    /**
     * NOR semantics: returns true only if ALL children evaluate to false (none is true).
     */
    private function processNot(Collection $node): bool
    {
        $rootNode = $node->getRoot();

        foreach ($node->sorted() as $item) {
            $processor = $this->getProcessorByNode($item);
            if (null === $processor) {
                continue;
            }

            $result = $processor->process($item);

            if ($rootNode->hasResult()) {
                return false;
            }

            if (true === $result) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if at least $count children evaluate to true.
     */
    private function processAtLeast(Collection $node): bool
    {
        $count = $node->getCount();
        if (null === $count) {
            throw new LogicException('Collection type "atLeast" requires a "count" value');
        }

        $rootNode = $node->getRoot();
        $trueCount = 0;
        $items = $node->sorted();
        $total = count($items);

        foreach ($items as $index => $item) {
            $processor = $this->getProcessorByNode($item);
            if (null === $processor) {
                continue;
            }

            $result = $processor->process($item);

            if ($rootNode->hasResult()) {
                return false;
            }

            if (true === $result) {
                $trueCount++;
                if ($trueCount >= $count) {
                    return true;
                }
            }

            // Early exit: remaining nodes cannot reach the required count
            $remaining = $total - $index - 1;
            if ($trueCount + $remaining < $count) {
                return false;
            }
        }

        return $trueCount >= $count;
    }

    /**
     * Returns true if exactly $count children evaluate to true.
     */
    private function processExactly(Collection $node): bool
    {
        $count = $node->getCount();
        if (null === $count) {
            throw new LogicException('Collection type "exactly" requires a "count" value');
        }

        $rootNode = $node->getRoot();
        $trueCount = 0;

        foreach ($node->sorted() as $item) {
            $processor = $this->getProcessorByNode($item);
            if (null === $processor) {
                continue;
            }

            $result = $processor->process($item);

            if ($rootNode->hasResult()) {
                return false;
            }

            if (true === $result) {
                $trueCount++;
            }
        }

        return $trueCount === $count;
    }
}
