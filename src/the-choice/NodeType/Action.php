<?php

namespace TheChoice\NodeType;

final class Action
{
    const STOP_ALWAYS = 'always';

    private $_action;
    private $_description = '';
    private $_stoppableType;

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
        return null === $this->_stoppableType;
    }
}