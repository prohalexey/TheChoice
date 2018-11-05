<?php

namespace TheChoice\NodeType;

abstract class AbstractCollection
{
    private $_collection = [];
    private $_description = '';

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
}