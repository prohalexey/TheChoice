<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Node\Collection;
use TheChoice\Node\Condition;
use TheChoice\Node\Context;
use TheChoice\Node\Root;
use TheChoice\Node\SwitchNode;
use TheChoice\Node\Value;

final class NodeFactoryResolver implements NodeFactoryResolverInterface
{
    /** @var array<string, class-string<NodeFactoryInterface>> */
    private array $factoryMap = [];

    /**
     * @param array<string, class-string<NodeFactoryInterface>> $factoryMap
     */
    public function __construct(array $factoryMap = [])
    {
        foreach (self::getDefaultMap() as $nodeType => $nodeFactoryClass) {
            $this->register($nodeType, $nodeFactoryClass);
        }

        foreach ($factoryMap as $nodeType => $nodeFactoryClass) {
            $this->register($nodeType, $nodeFactoryClass);
        }
    }

    /**
     * @param class-string<NodeFactoryInterface> $nodeFactoryClass
     */
    public function register(string $nodeType, string $nodeFactoryClass): self
    {
        if (!is_a($nodeFactoryClass, NodeFactoryInterface::class, true)) {
            throw new InvalidArgumentException(
                sprintf('Node factory class "%s" must implement %s', $nodeFactoryClass, NodeFactoryInterface::class),
            );
        }

        $this->factoryMap[$nodeType] = $nodeFactoryClass;

        return $this;
    }

    /**
     * @return class-string<NodeFactoryInterface>
     */
    public function resolve(string $nodeType): string
    {
        if (!array_key_exists($nodeType, $this->factoryMap)) {
            throw new InvalidArgumentException(
                sprintf('Node type "%s" is not supported.', $nodeType),
            );
        }

        return $this->factoryMap[$nodeType];
    }

    /**
     * @return array<string, class-string<NodeFactoryInterface>>
     */
    private static function getDefaultMap(): array
    {
        return [
            Condition::getNodeName()  => NodeConditionFactory::class,
            Context::getNodeName()    => NodeContextFactory::class,
            Collection::getNodeName() => NodeCollectionFactory::class,
            Root::getNodeName()       => NodeRootFactory::class,
            Value::getNodeName()      => NodeValueFactory::class,
            SwitchNode::getNodeName() => NodeSwitchFactory::class,
        ];
    }
}
