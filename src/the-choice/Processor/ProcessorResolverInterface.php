<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use TheChoice\Node\Node;

interface ProcessorResolverInterface
{
    /**
     * @return class-string
     */
    public function resolve(Node $node): string;
}
