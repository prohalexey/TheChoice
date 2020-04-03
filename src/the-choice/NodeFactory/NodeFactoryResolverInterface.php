<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

interface NodeFactoryResolverInterface
{
    public function resolve(string $nodeType);
}