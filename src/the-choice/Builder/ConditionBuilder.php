<?php

declare(strict_types=1);

namespace TheChoice\Builder;

use TheChoice\Exception\LogicException;
use TheChoice\Node\Condition;

final class ConditionBuilder implements NodeBuilderInterface
{
    private ?NodeBuilderInterface $ifBuilder = null;

    private ?NodeBuilderInterface $thenBuilder = null;

    private ?NodeBuilderInterface $elseBuilder = null;

    private int $priority = 0;

    private ?string $description = null;

    public function if(NodeBuilderInterface $builder): self
    {
        $this->ifBuilder = $builder;

        return $this;
    }

    public function then(NodeBuilderInterface $builder): self
    {
        $this->thenBuilder = $builder;

        return $this;
    }

    public function else(NodeBuilderInterface $builder): self
    {
        $this->elseBuilder = $builder;

        return $this;
    }

    public function priority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function build(): Condition
    {
        if (null === $this->ifBuilder) {
            throw new LogicException('Condition "if" node must be provided before calling build()');
        }

        if (null === $this->thenBuilder) {
            throw new LogicException('Condition "then" node must be provided before calling build()');
        }

        $node = new Condition(
            $this->ifBuilder->build(),
            $this->thenBuilder->build(),
            $this->elseBuilder?->build(),
        );

        if (0 !== $this->priority) {
            $node->setPriority($this->priority);
        }

        if (null !== $this->description) {
            $node->setDescription($this->description);
        }

        return $node;
    }
}
