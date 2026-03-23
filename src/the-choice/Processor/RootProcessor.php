<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use InvalidArgumentException;
use TheChoice\Node\Collection;
use TheChoice\Node\Condition;
use TheChoice\Node\Context;
use TheChoice\Node\HasChildNodes;
use TheChoice\Node\Node;
use TheChoice\Node\Root;
use TheChoice\Node\SwitchNode;
use TheChoice\Node\Value;
use TheChoice\Trace\EvaluationTrace;
use TheChoice\Trace\TraceCollector;
use TheChoice\Trace\TraceEntry;

class RootProcessor extends AbstractProcessor
{
    public function process(Node $node): mixed
    {
        if (!$node instanceof Root) {
            throw new InvalidArgumentException('Node must be an instance of Root');
        }

        $this->flushAllProcessors($node);

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

    /**
     * Processes the node tree with tracing enabled, returning an EvaluationTrace
     * that contains both the result and the full trace tree.
     */
    public function processWithTrace(Node $node): EvaluationTrace
    {
        if (!$node instanceof Root) {
            throw new InvalidArgumentException('Node must be an instance of Root');
        }

        $collector = new TraceCollector();
        $this->setTraceCollector($collector);

        try {
            $this->flushAllProcessors($node);

            $collector->begin('Root', 'root');

            $rules = $node->getRules();
            $processor = $this->getProcessorByNode($rules);

            $result = null;
            if (null !== $processor) {
                $result = $processor->process($rules);
                if ($node->hasResult()) {
                    $result = $node->getResult();
                }
            }

            $collector->end($result);

            $rootEntry = $collector->getRoot();
            if (null === $rootEntry) {
                $rootEntry = new TraceEntry('Root', 'root', $result);
            }

            return new EvaluationTrace($result, $rootEntry);
        } finally {
            $this->setTraceCollector(null);
        }
    }

    /**
     * Calls flush() on every registered processor so that any cached state
     * (e.g. memoised context results in ContextProcessor) is cleared before
     * each rule evaluation.
     */
    private function flushAllProcessors(Root $root): void
    {
        $container = $this->getContainer();

        /** @var ProcessorResolverInterface $resolver */
        $resolver = $container->get(ProcessorResolverInterface::class);

        $processorClasses = [];
        foreach ($this->iterateNodes($root) as $node) {
            $processorClasses[$resolver->resolve($node)] = true;
        }

        foreach (array_keys($processorClasses) as $processorClass) {
            /** @var AbstractProcessor $processor */
            $processor = $container->get($processorClass);
            $processor->flush();
        }
    }

    /**
     * @return iterable<Node>
     */
    private function iterateNodes(Node $node): iterable
    {
        yield $node;

        if ($node instanceof Root) {
            yield from $this->iterateNodes($node->getRules());

            return;
        }

        if ($node instanceof Condition) {
            yield from $this->iterateNodes($node->getIfNode());
            yield from $this->iterateNodes($node->getThenNode());

            $elseNode = $node->getElseNode();
            if (null !== $elseNode) {
                yield from $this->iterateNodes($elseNode);
            }

            return;
        }

        if ($node instanceof Collection) {
            foreach ($node->all() as $childNode) {
                yield from $this->iterateNodes($childNode);
            }

            return;
        }

        if ($node instanceof Context || $node instanceof Value) {
            return;
        }

        if ($node instanceof SwitchNode) {
            foreach ($node->getCases() as $case) {
                yield from $this->iterateNodes($case->getThenNode());
            }

            $defaultNode = $node->getDefaultNode();
            if (null !== $defaultNode) {
                yield from $this->iterateNodes($defaultNode);
            }

            return;
        }

        // Allow custom node types to expose their children for the flush pass
        // by implementing the HasChildNodes interface.
        if ($node instanceof HasChildNodes) {
            foreach ($node->getChildNodes() as $child) {
                yield from $this->iterateNodes($child);
            }
        }
    }
}
