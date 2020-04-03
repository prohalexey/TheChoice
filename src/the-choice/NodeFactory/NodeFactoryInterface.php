<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

use TheChoice\Builder\BuilderInterface;

interface NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure);
}