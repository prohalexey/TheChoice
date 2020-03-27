<?php

declare(strict_types=1);

namespace TheChoice\Node;

class Condition extends AbstractChildNode implements Sortable
{
    protected $ifNode;
    protected $thenNode;
    protected $elseNode;
    protected $priority;

    public function __construct(Node $ifNode, Node $thenNode, Node $elseNode = null)
    {
        $this->ifNode = $ifNode;
        $this->thenNode = $thenNode;
        $this->elseNode = $elseNode;
    }

    public static function getNodeName(): string
    {
        return 'condition';
    }

    public function setPriority(int $priority)
    {
        $this->priority = $priority;
        return $this;
    }

    public function getIfNode()
    {
        return $this->ifNode;
    }

    public function getThenNode()
    {
        return $this->thenNode;
    }

    public function getElseNode()
    {
        return $this->elseNode;
    }

    public function getSortableValue()
    {
        return $this->priority;
    }
}