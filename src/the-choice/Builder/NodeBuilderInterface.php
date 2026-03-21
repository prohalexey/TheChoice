<?php

declare(strict_types=1);

namespace TheChoice\Builder;

use TheChoice\Node\Node;

/**
 * Common interface for all fluent rule builder classes.
 * Each builder accumulates configuration via chained setter-methods and
 * materialises a concrete {@see Node} on the final {@see build()} call.
 */
interface NodeBuilderInterface
{
    public function build(): Node;
}
