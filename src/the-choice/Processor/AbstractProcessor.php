<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use Psr\Container\ContainerInterface;
use TheChoice\Node\Node;
use TheChoice\Trace\TraceCollector;

abstract class AbstractProcessor implements ProcessorInterface
{
    protected ContainerInterface $container;

    protected ?TraceCollector $traceCollector = null;

    /** @var array<string, class-string> */
    protected array $processorResolvingCache = [];

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function setTraceCollector(?TraceCollector $traceCollector): void
    {
        $this->traceCollector = $traceCollector;
    }

    public function getTraceCollector(): ?TraceCollector
    {
        return $this->traceCollector;
    }

    public function flush(): void
    {
        // Default implementation: nothing to flush.
        // Override in processors that maintain internal state.
    }

    public function getProcessorByNode(Node $node): ?self
    {
        $nodeType = $node::class;
        if (!array_key_exists($nodeType, $this->processorResolvingCache)) {
            /** @var ProcessorResolverInterface $processorResolver */
            $processorResolver = $this->getContainer()->get(ProcessorResolverInterface::class);
            $this->processorResolvingCache[$nodeType] = $processorResolver->resolve($node);
        }

        // @phpstan-ignore-next-line
        $processor = $this->getContainer()->get($this->processorResolvingCache[$nodeType]);

        if ($processor instanceof self && null !== $this->traceCollector) {
            $processor->setTraceCollector($this->traceCollector);
        }

        return $processor;
    }

    abstract public function process(Node $node): mixed;
}
