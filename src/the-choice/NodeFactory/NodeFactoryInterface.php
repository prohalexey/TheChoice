<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

use TheChoice\Builder\BuilderInterface;
use TheChoice\Node\Node;

interface NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure): Node;
}
