<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use InvalidArgumentException;
use TheChoice\Node\Collection;
use TheChoice\Node\Condition;
use TheChoice\Node\Context;
use TheChoice\Node\Node;
use TheChoice\Node\Root;
use TheChoice\Node\Value;

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
    }
}
