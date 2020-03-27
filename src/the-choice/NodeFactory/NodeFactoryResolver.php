<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

use TheChoice\Node\{
    Collection,
    Condition,
    Context,
    Root,
    Value
};

use TheChoice\Exception\InvalidArgumentException;

final class NodeFactoryResolver implements NodeFactoryResolverInterface
{
    public function resolve(string $nodeType)
    {
        $nodeTypeMap = $this->getNodeTypeMap();

        if (!array_key_exists($nodeType, $nodeTypeMap)) {
            throw new InvalidArgumentException(sprintf('Unknown node type "%s"', $nodeType));
        }

        return $nodeTypeMap[$nodeType];
    }

    private function getNodeTypeMap(): array
    {
        return [
            Condition::getNodeName()  => NodeConditionFactory::class,
            Context::getNodeName()    => NodeContextFactory::class,
            Collection::getNodeName() => NodeCollectionFactory::class,
            Root::getNodeName()       => NodeRootFactory::class,
            Value::getNodeName()      => NodeValueFactory::class,
        ];
    }
}