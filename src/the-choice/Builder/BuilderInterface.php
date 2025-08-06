<?php

declare(strict_types=1);

namespace TheChoice\Builder;

use Psr\Container\ContainerInterface;
use TheChoice\Node\Node;
use TheChoice\Node\Root;

interface BuilderInterface
{
    public function build(array &$structure): Node;

    public function getContainer(): ContainerInterface;

    public function setRoot(Root $rootNode): self;

    public function getRoot(): Root;
}
