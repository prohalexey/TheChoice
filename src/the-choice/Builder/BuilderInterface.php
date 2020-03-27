<?php

namespace TheChoice\Builder;

use Psr\Container\ContainerInterface;
use TheChoice\Node\Root;

interface BuilderInterface
{
    public function build(&$structure);

    public function getContainer(): ContainerInterface;

    public function setRoot(Root $rootNode): BuilderInterface;

    public function getRoot(): Root;
}