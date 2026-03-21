<?php

declare(strict_types=1);

namespace TheChoice\Builder;

use TheChoice\Node\Collection;

final class CollectionBuilder implements NodeBuilderInterface
{
    /** @var array<NodeBuilderInterface> */
    private array $builders = [];

    private ?int $count = null;

    private int $priority = 0;

    private ?string $description = null;

    public function __construct(private readonly string $type)
    {
    }

    public function add(NodeBuilderInterface $builder): self
    {
        $this->builders[] = $builder;

        return $this;
    }

    /**
     * Required for {@see Collection::TYPE_AT_LEAST} and {@see Collection::TYPE_EXACTLY}.
     */
    public function count(int $count): self
    {
        $this->count = $count;

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

    public function build(): Collection
    {
        $node = new Collection($this->type);

        foreach ($this->builders as $builder) {
            $node->add($builder->build());
        }

        if (null !== $this->count) {
            $node->setCount($this->count);
        }

        if (0 !== $this->priority) {
            $node->setPriority($this->priority);
        }

        if (null !== $this->description) {
            $node->setDescription($this->description);
        }

        return $node;
    }
}
