<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use Psr\Container\ContainerInterface;

use TheChoice\Node\Node;

abstract class AbstractProcessor
{
    protected $container;

    protected $processorResolvingCache = [];

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function getProcessorByNode(Node $node)
    {
        $nodeType = get_class($node);
        if (!array_key_exists($nodeType, $this->processorResolvingCache)) {
            $processorResolver = $this->getContainer()->get(ProcessorResolverInterface::class);
            $this->processorResolvingCache[$nodeType] = $processorResolver->resolve($node);
        }

        return $this->getContainer()->get($this->processorResolvingCache[$nodeType]);
    }
}