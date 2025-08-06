<?php

declare(strict_types=1);

namespace TheChoice\Node;

abstract class AbstractChildNode extends AbstractNode
{
    protected Root $root;

    public function setRoot(Root $root): Node
    {
        $this->root = $root;

        return $this;
    }

    public function getRoot(): Root
    {
        return $this->root;
    }
}
