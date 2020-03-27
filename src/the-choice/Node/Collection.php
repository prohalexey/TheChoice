<?php

declare(strict_types=1);

namespace TheChoice\Node;

use TheChoice\Exception\LogicException;

class Collection extends AbstractChildNode implements Sortable
{
    const TYPE_AND = 'and';
    const TYPE_OR = 'or';

    protected $type;
    protected $collection = [];
    protected $priority;

    public function __construct($type)
    {
        if ($type !== self::TYPE_AND && $type !== self::TYPE_OR) {
            throw new LogicException(sprintf('Collection type must be "or" or "and". "%s" given', $type));
        }

        $this->type = $type;
    }

    public static function getNodeName(): string
    {
        return 'collection';
    }

    public function getType()
    {
        return $this->type;
    }

    public function add($element): self
    {
        $this->collection[] = $element;
        return $this;
    }

    /**
     * @return Node[]
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
        usort($this->collection, static function($element1, $element2) {
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

    public function getSortableValue()
    {
        return $this->priority;
    }
}