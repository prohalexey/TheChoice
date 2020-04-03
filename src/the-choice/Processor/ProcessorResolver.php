<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use TheChoice\Node\{
    Node,

    Collection,
    Condition,
    Context,
    Root,
    Value
};

use TheChoice\Exception\InvalidArgumentException;


class ProcessorResolver implements ProcessorResolverInterface
{
    public function resolve(Node $node)
    {
        $processorMap = $this->getProcessorMap();

        $nodeType = get_class($node);

        if (!array_key_exists($nodeType, $processorMap)) {
            throw new InvalidArgumentException(sprintf('Unknown operator type "%s"', $nodeType));
        }

        return $processorMap[$nodeType];
    }

    public function getProcessorMap(): array
    {
        return [
            Root::class => RootProcessor::class,
            Condition::class => ConditionProcessor::class,
            Collection::class => CollectionProcessor::class,
            Value::class => ValueProcessor::class,
            Context::class => ContextProcessor::class,
        ];
    }
}