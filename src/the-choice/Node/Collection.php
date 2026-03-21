<?php

declare(strict_types=1);

namespace TheChoice\Node;

use TheChoice\Exception\LogicException;

class Collection extends AbstractChildNode implements Sortable
{
    public const TYPE_AND = 'and';

    public const TYPE_OR = 'or';

    public const TYPE_NOT = 'not';

    public const TYPE_AT_LEAST = 'atLeast';

    public const TYPE_EXACTLY = 'exactly';

    protected string $type;

    /** @var array<Node> */
    protected array $collection = [];

    protected int $priority = 0;

    protected ?int $count = null;

    public function __construct(string $type)
    {
        $allowed = [
            self::TYPE_AND,
            self::TYPE_OR,
            self::TYPE_NOT,
            self::TYPE_AT_LEAST,
            self::TYPE_EXACTLY,
        ];
        if (!in_array($type, $allowed, true)) {
            throw new LogicException(sprintf(
                'Collection type must be one of "%s". "%s" given',
                implode('", "', $allowed),
                $type,
            ));
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

    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
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
