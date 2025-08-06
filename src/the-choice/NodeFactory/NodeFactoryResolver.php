<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Node\Collection;
use TheChoice\Node\Condition;
use TheChoice\Node\Context;
use TheChoice\Node\Root;
use TheChoice\Node\Value;

final class NodeFactoryResolver implements NodeFactoryResolverInterface
{
    /**
     * @return class-string
     */
    public function resolve(string $nodeType): string
    {
        return match ($nodeType) {
            Condition::getNodeName()  => NodeConditionFactory::class,
            Context::getNodeName()    => NodeContextFactory::class,
            Collection::getNodeName() => NodeCollectionFactory::class,
            Root::getNodeName()       => NodeRootFactory::class,
            Value::getNodeName()      => NodeValueFactory::class,

            default => throw new InvalidArgumentException(
                sprintf('Node type "%s" is not supported.', $nodeType),
            ),
        };
    }
}
