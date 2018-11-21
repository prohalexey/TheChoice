<?php

namespace TheChoice\NodeType;

use TheChoice\Contracts\Sortable;

abstract class AbstractCollection implements Sortable
{
    private $_collection = [];
    private $_description = '';
    private $_priority;

    public function add($element)
    {
        $this->_collection[] = $element;
        return $this;
    }

    public function all(): array
    {
        return $this->_collection;
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