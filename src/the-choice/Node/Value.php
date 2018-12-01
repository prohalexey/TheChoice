<?php

namespace TheChoice\Node;

final class Value
{
    private $_tree;

    private $_value;
    private $_description = '';

    public function __construct($value)
    {
        $this->_value = $value;
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

    public function setDescription(string $description)
    {
        $this->_description = $description;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->_description;
    }

    public function getValue()
    {
        return $this->_value;
    }
}