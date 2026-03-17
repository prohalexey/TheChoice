<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use TheChoice\Node\Node;

interface ProcessorInterface
{
    public function process(Node $node): mixed;

    /**
     * Clears any internal cached state (e.g. memoised context results).
     * Called automatically by RootProcessor at the start of each rule evaluation.
     */
    public function flush(): void;
}
