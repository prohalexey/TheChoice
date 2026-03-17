<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use Psr\Container\ContainerInterface;
use TheChoice\Node\Node;

abstract class AbstractProcessor implements ProcessorInterface
{
    protected ContainerInterface $container;

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
        return $this->getContainer()->get($this->processorResolvingCache[$nodeType]);
    }

    abstract public function process(Node $node): mixed;
}
