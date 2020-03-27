<?php

declare(strict_types=1);

namespace TheChoice\Context;

use Psr\Container\ContainerInterface;

use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Node\Context;

class ContextFactory implements ContextFactoryInterface
{
    protected $contextMap;

    /** @var ContainerInterface */
    protected $container;

    public function __construct(array $contexts = [])
    {
        $this->contextMap = $contexts;
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function createContextFromContextNode(Context $node): ContextInterface
    {
        $contextName = $node->getContextName();

        $context = $this->getContext($contextName);
        $context = $this->setParamsToObject($context, $node->getParams());

        return $context;
    }

    private function getContext($contextType)
    {
        if (!array_key_exists($contextType, $this->contextMap)) {
            throw new InvalidArgumentException(sprintf('Context type "%s" not found', $contextType));
        }

        $context = $this->contextMap[$contextType];

        if (is_object($context)) {
            if (!$context instanceof ContextInterface) {
                throw new InvalidArgumentException(
                    sprintf('Object "%s" not implements ContextInterface', get_class($context))
                );
            }

            return $context;
        }

        if (is_string($context)) {
            $context = $this->getContextFromString($context);

            if (is_object($context) && !$context instanceof ContextInterface) {
                throw new InvalidArgumentException(
                    sprintf('Object "%s" not implements ContextInterface', $context)
                );
            }

            return $context;
        }

        if (is_callable($context)) {
            return new CallableContext($context);
        }

        throw new InvalidArgumentException(sprintf('Unknown context type "%s"', $contextType));
    }

    private function setParamsToObject($object, array $params)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException(sprintf('Params can be set to objects only, %s given', gettype($object)));
        }

        foreach ($params as $paramName => $paramValue) {
            $commonSetterName = sprintf('set%s', ucfirst($paramName));
            if (method_exists($object, $commonSetterName)) {
                $object->{$commonSetterName}($paramValue);
            } elseif (property_exists($object, $paramName)) {
                $object->{$paramName} = $paramValue;
            } else {
                trigger_error(vsprintf('Object %s does not have public property %s or %s setter', [
                    get_class($object),
                    $paramName,
                    $commonSetterName
                ]));
            }
        }

        return $object;
    }

    private function getContextFromString(string $context)
    {
        if (null !== $this->container && $this->container->has($context)) {
            return $this->container->get($context);
        }

        if (class_exists($context)) {
            return new $context;
        }

        throw new InvalidArgumentException(sprintf('Cannot find "%s" context', $context));
    }
}