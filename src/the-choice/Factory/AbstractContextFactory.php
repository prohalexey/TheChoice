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