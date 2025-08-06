<?php

declare(strict_types=1);

namespace TheChoice\Context;

use Psr\Container\ContainerInterface;
use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Node\Context;

class ContextFactory implements ContextFactoryInterface
{
    protected array $contextMap;

    protected ?ContainerInterface $container = null;

    public function __construct(array $contexts = [])
    {
        $this->contextMap = $contexts;
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function createContextFromContextNode(Context $node): ContextInterface
    {
        $contextName = $node->getContextName();
        if (null === $contextName) {
            throw new InvalidArgumentException('Context name cannot be null');
        }

        $context = $this->getContext($contextName);

        return $this->setParamsToObject($context, $node->getParams());
    }

    private function getContext(string $contextType): ContextInterface
    {
        if (!array_key_exists($contextType, $this->contextMap)) {
            throw new InvalidArgumentException(sprintf('Context type "%s" not found', $contextType));
        }

        $context = $this->contextMap[$contextType];

        if (is_object($context)) {
            if (!$context instanceof ContextInterface) {
                throw new InvalidArgumentException(
                    sprintf('Object "%s" not implements ContextInterface', $context::class),
                );
            }

            return $context;
        }

        if (is_string($context)) {
            return $this->getContextFromString($context);
        }

        if (is_callable($context)) {
            return new CallableContext($context);
        }

        throw new InvalidArgumentException(sprintf('Unknown context type "%s"', $contextType));
    }

    private function setParamsToObject(object $object, array $params): ContextInterface
    {
        foreach ($params as $paramName => $paramValue) {
            $commonSetterName = sprintf('set%s', ucfirst($paramName));
            if (method_exists($object, $commonSetterName)) {
                $object->{$commonSetterName}($paramValue);
            } elseif (property_exists($object, $paramName)) {
                $object->{$paramName} = $paramValue;
            } else {
                trigger_error(vsprintf('Object %s does not have public property %s or %s setter', [
                    $object::class,
                    $paramName,
                    $commonSetterName,
                ]));
            }
        }

        if (!$object instanceof ContextInterface) {
            throw new InvalidArgumentException('Object must implement ContextInterface');
        }

        return $object;
    }

    private function getContextFromString(string $context): ContextInterface
    {
        if (null !== $this->container && $this->container->has($context)) {
            $result = $this->container->get($context);
            if (!$result instanceof ContextInterface) {
                throw new InvalidArgumentException('Container returned object that does not implement ContextInterface');
            }

            return $result;
        }

        if (class_exists($context)) {
            $result = new $context();
            if (!$result instanceof ContextInterface) {
                throw new InvalidArgumentException('Class does not implement ContextInterface');
            }

            return $result;
        }

        throw new InvalidArgumentException(sprintf('Cannot find "%s" context', $context));
    }
}
