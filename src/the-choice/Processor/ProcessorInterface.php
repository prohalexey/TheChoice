<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use TheChoice\Node\Node;

interface ProcessorInterface
{
    public function process(Node $node): mixed;
}
