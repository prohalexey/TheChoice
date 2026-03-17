<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

interface NodeFactoryResolverInterface
{
    /**
     * @param class-string<NodeFactoryInterface> $nodeFactoryClass
     */
    public function register(string $nodeType, string $nodeFactoryClass): self;

    public function resolve(string $nodeType): string;
}
