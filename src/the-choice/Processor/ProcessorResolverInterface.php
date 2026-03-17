<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use TheChoice\Node\Node;

interface ProcessorResolverInterface
{
    /**
     * @param class-string<Node>              $nodeClass
     * @param class-string<AbstractProcessor> $processorClass
     */
    public function register(string $nodeClass, string $processorClass): self;

    /**
     * @return class-string<AbstractProcessor>
     */
    public function resolve(Node $node): string;
}
