<?php

namespace TheChoice;

use TheChoice\Contracts\OperatorFactoryInterface;
use TheChoice\Contracts\OperatorInterface;

class OperatorFactory implements OperatorFactoryInterface
{
    private $_typeMap;

    public function __construct(array $typeMap)
    {
        $this->_typeMap = $typeMap;
    }

    public function create(string $type, $value): OperatorInterface
    {
        if (!array_key_exists($type, $this->_typeMap)) {
            throw new \InvalidArgumentException(sprintf('Unknown type "%s"', $type));
        }

        $className = $this->_typeMap[$type];

        return new $className($value);
    }
}