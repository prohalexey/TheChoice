<?php

declare(strict_types=1);

namespace TheChoice\Node;

class Condition extends AbstractChildNode implements Sortable
{
    protected Node $ifNode;

    protected Node $thenNode;

    protected ?Node $elseNode;

    protected int $priority = 0;

    public function __construct(Node $ifNode, Node $thenNode, ?Node $elseNode = null)
    {
        $this->ifNode = $ifNode;
        $this->thenNode = $thenNode;
        $this->elseNode = $elseNode;
    }

    public static function getNodeName(): string
    {
        return 'condition';
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getIfNode(): Node
    {
        return $this->ifNode;
    }

    public function getThenNode(): Node
    {
        return $this->thenNode;
    }

    public function getElseNode(): ?Node
    {
        return $this->elseNode;
    }

    public function getSortableValue(): int
    {
        return $this->priority;
    }
}
