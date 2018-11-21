<?php

namespace TheChoice\NodeType;

use TheChoice\Contracts\Sortable;

final class Condition implements Sortable
{
    private $_if;
    private $_then;
    private $_else;
    private $_description = '';
    private $_priority;

    public function __construct($if, $then, $else = null)
    {
        $this->_if = $if;
        $this->_then = $then;
        $this->_else = $else;
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

    public function getDescription(): string
    {
        return $this->_description;
    }

    public function getIf()
    {
        return $this->_if;
    }

    public function getThen()
    {
        return $this->_then;
    }

    public function getElse()
    {
        return $this->_else;
    }

    public function getSortableValue()
    {
        return $this->_priority;
    }
}