<?php

namespace TheChoice\NodeType;

use TheChoice\Contracts\Sortable;

final class Action implements Sortable
{
    const STOP_ALWAYS = 'always';

    private $_action;
    private $_description = '';
    private $_stoppableType;
    private $_priority;
    private $_params = [];

    public function __construct(string $action)
    {
        $this->_action = $action;
    }

    public function setDescription(string $description)
    {
        $this->_description = $description;
        return $this;
    }

    public function getAction(): string
    {
        return $this->_action;
    }

    public function getDescription(): string
    {
        return $this->_description;
    }

    public function setPriority(int $priority)
    {
        $this->_priority = $priority;
        return $this;
    }

    public function getStoppableType()
    {
        return $this->_stoppableType;
    }

    public function setStoppableType($type)
    {
        $this->_stoppableType = $type;

        return $this;
    }

    public function isStoppable(): bool
    {
        return null !== $this->_stoppableType;
    }

    public function getSortableValue()
    {
        return $this->_priority;
    }

    public function setParams(array $params)
    {
        $this->_params = $params;
    }

    public function getParams()
    {
        return $this->_params;
    }
}