<?php

namespace TheChoice\Factory;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use TheChoice\CallableActionContext;

abstract class AbstractContextFactory
{
    private $_contextMap;

    /** @var ContainerInterface */
    private $container;

    public function __construct(array $actions)
    {
        $this->_contextMap = $actions;
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function getContext($contextType)
    {
        if (!array_key_exists($contextType, $this->_contextMap)) {
            throw new \InvalidArgumentException(sprintf('Type "%s" is not bound', $contextType));
        }

        $context = $this->_contextMap[$contextType];

        if (\is_object($context)) {
            $this->checkType($context);

            return $context;
        }

        if (\is_string($context)) {
            $context = $this->getContextFromString($context);

            $this->checkType($context);

            return $context;
        }

        if (\is_callable($context)) {
            return new CallableActionContext($context);
        }

        throw new \InvalidArgumentException(sprintf('Unknown action type "%s"', $contextType));
    }

    abstract protected function checkType($context);

    protected function setParamsToObject($object, array $params)
    {
        if (!\is_object($object)) {
            throw new \InvalidArgumentException(sprintf('Params can be set to objects only, %s given', \gettype($object)));
        }

        foreach ($params as $paramName => $paramValue) {
            $commonSetterName = sprintf('set%s', ucfirst($paramName));
            if (method_exists($object, $commonSetterName)) {
                $object->{$commonSetterName}($paramValue);
            } elseif (property_exists($object, $paramName)) {
                $object->{$paramName} = $paramValue;
            } else {
                trigger_error(vsprintf('Object %s doesn\'t have public property %s or %s setter', [
                    \get_class($object),
                    $paramName,
                    $commonSetterName
                ]));
            }
        }

        return $object;
    }

    private function getContextFromString(string $context)
    {
        if (null !== $this->container) {
            try {
                $context = $this->container->get($context);
            } catch(ContainerExceptionInterface $e) {}
        } else {
            $context = new $context;
        }

        return $context;
    }
}