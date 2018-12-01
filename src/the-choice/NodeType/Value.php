<?php

namespace TheChoice\NodeType;

final class Value
{
    private $_value;
    private $_description = '';

    public function __construct($value)
    {
        $this->_value = $value;
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