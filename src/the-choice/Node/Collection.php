<?php

namespace TheChoice\Node;

use TheChoice\Contract\Sortable;

class Collection implements Sortable
{
    const TYPE_AND = 'and';
    const TYPE_OR = 'or';

    private $_tree;

    private $_type;
    private $_collection = [];
    private $_description = '';
    private $_priority;

    public function __construct($type)
    {
        if ($type !== self::TYPE_AND && $type !== self::TYPE_OR) {
            throw new \LogicException(sprintf('Collection type must be "or" or "and". "%s" given', $type));
        }

        $this->_type = $type;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function add($element)
    {
        $this->_collection[] = $element;
        return $this;
    }

    public function all(): array
    {
        return $this->_collection;
    }

    public function setTree(Tree $tree)
    {
        $this->_tree = $tree;
    }

    /** @return Tree|null */
    public function getTree()
    {
        return $this->_tree;
    }

    public function getDescription(): string
    {
        return $this->_description;
    }

    public function setDescription(string $description)
    {
        $this->_description = $description;
        return $this;
    }

    public function setPriority(int $priority)
    {
        $this->_priority = $priority;
        return $this;
    }

    public function sort()
    {
        usort($this->_collection, function($element1, $element2) {
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
        return $this->_priority;
    }
}