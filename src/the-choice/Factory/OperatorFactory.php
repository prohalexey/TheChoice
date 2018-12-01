<?php

namespace TheChoice\Factory;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use TheChoice\Contract\OperatorFactoryInterface;
use TheChoice\Contract\OperatorInterface;

use TheChoice\Operator\ArrayContain;
use TheChoice\Operator\ArrayNotContain;
use TheChoice\Operator\Equal;
use TheChoice\Operator\GreaterThan;
use TheChoice\Operator\GreaterThanOrEqual;
use TheChoice\Operator\LowerThan;
use TheChoice\Operator\LowerThanOrEqual;
use TheChoice\Operator\NotEqual;
use TheChoice\Operator\NumericInRange;
use TheChoice\Operator\StringContain;
use TheChoice\Operator\StringNotContain;

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