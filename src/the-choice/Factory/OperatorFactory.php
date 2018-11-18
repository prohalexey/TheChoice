<?php

namespace TheChoice\Factory;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use TheChoice\Contracts\OperatorFactoryInterface;
use TheChoice\Contracts\OperatorInterface;

use TheChoice\Operators\ArrayContain;
use TheChoice\Operators\ArrayNotContain;
use TheChoice\Operators\Equal;
use TheChoice\Operators\GreaterThan;
use TheChoice\Operators\GreaterThanOrEqual;
use TheChoice\Operators\LowerThan;
use TheChoice\Operators\LowerThanOrEqual;
use TheChoice\Operators\NotEqual;
use TheChoice\Operators\NumericInRange;
use TheChoice\Operators\StringContain;
use TheChoice\Operators\StringNotContain;

class OperatorFactory implements OperatorFactoryInterface
{
    private $_typeMap;

    /** @var ContainerInterface */
    private $container;

    public function __construct()
    {
        $this->_typeMap = [
            'arrayContain' => ArrayContain::class,
            'arrayNotContain' => ArrayNotContain::class,
            'equal' => Equal::class,
            'notEqual' => NotEqual::class,
            'greaterThan' => GreaterThan::class,
            'greaterThanOrEqual' => GreaterThanOrEqual::class,
            'lowerThan' => LowerThan::class,
            'lowerThanOrEqual' => LowerThanOrEqual::class,
            'stringContain' => StringContain::class,
            'stringNotContain' => StringNotContain::class,
            'numericInRange' => NumericInRange::class,
        ];
    }

    public function registerOperator(string $type, string $operator)
    {
        $this->_typeMap[$type] = $operator;
        return $this;
    }

    public function unregisterOperator(string $type)
    {
        unset($this->_typeMap[$type]);
        return $this;
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