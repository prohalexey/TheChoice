<?php

namespace TheChoice;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use TheChoice\Contracts\OperatorFactoryInterface;
use TheChoice\Contracts\OperatorInterface;

class OperatorFactory implements OperatorFactoryInterface
{
    private $_typeMap;

    /** @var ContainerInterface */
    private $container;

    public function __construct(array $typeMap)
    {
        $this->_typeMap = $typeMap;
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(string $type, $value): OperatorInterface
    {
        if (!array_key_exists($type, $this->_typeMap)) {
            throw new \InvalidArgumentException(sprintf('Unknown type "%s"', $type));
        }

        $className = $this->_typeMap[$type];

        /** @var OperatorInterface $operator */
        $operator = null;

        if (null !== $this->container) {
            try {
                $operator = $this->container->get($className);
            } catch (ContainerExceptionInterface $e) {}
        } else {
            $operator = new $className;
        }

        if (!$operator instanceof OperatorInterface) {
            throw new \InvalidArgumentException(sprintf('Object mapped to the type "%s" does not implements OperatorInterface', $type));
        }

        $operator->setValue($value);

        return $operator;
    }
}