<?php

declare(strict_types=1);

namespace TheChoice\Node;

use TheChoice\Exception\LogicException;

class Collection extends AbstractChildNode implements Sortable
{
    public const TYPE_AND = 'and';

    public const TYPE_OR = 'or';

    protected string $type;

    /** @var array<Node> */
    protected array $collection = [];

    protected int $priority = 0;

    public function __construct(string $type)
    {
        if (!in_array($type, [self::TYPE_AND, self::TYPE_OR], true)) {
            throw new LogicException(sprintf('Collection type must be "or" or "and". "%s" given', $type));
        }

        $this->type = $type;
    }

    public static function getNodeName(): string
    {
        return 'collection';
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function add(Node $element): self
    {
        $this->collection[] = $element;

        return $this;
    }

    /**
     * @return array<Node>
     */
    public function all(): array
    {
        return $this->collection;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function sort(): self
    {
        usort($this->collection, static function ($element1, $element2): int {
            if (!$element2 instanceof Sortable) {
                return 1;
            }

            if (!$element1 instanceof Sortable) {
                return -1;
            }

            return $element1->getSortableValue() <=> $element2->getSortableValue();
        });

        return $this;
    }

    public function getSortableValue(): int
    {
        return $this->priority;
    }
}
