<?php

namespace TheChoice;

final class Collection
{
    const TYPE_OR = 'or';
    const TYPE_AND = 'and';

    private $_collection = [];
    private $_type;

    public function __construct($type)
    {
        if ($type !== self::TYPE_OR && $type !== self::TYPE_AND) {
            throw new \InvalidArgumentException(sprintf('Invalid collection type. Must be "or" or "and". "%s" given', $type));
        }

        $this->_type = $type;
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

    public function getType()
    {
        return $this->_type;
    }
}