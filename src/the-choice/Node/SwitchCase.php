<?php

declare(strict_types=1);

namespace TheChoice\Node;

use TheChoice\Operator\OperatorInterface;

final readonly class SwitchCase
{
    public function __construct(
        private OperatorInterface $operator,
        private Node $thenNode,
    ) {
    }

    public function getOperator(): OperatorInterface
    {
        return $this->operator;
    }

    public function getThenNode(): Node
    {
        return $this->thenNode;
    }
}
