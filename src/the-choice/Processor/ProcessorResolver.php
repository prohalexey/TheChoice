<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Node\Collection;
use TheChoice\Node\Condition;
use TheChoice\Node\Context;
use TheChoice\Node\Node;
use TheChoice\Node\Root;
use TheChoice\Node\Value;

class ProcessorResolver implements ProcessorResolverInterface
{
    /**
     * @return class-string<AbstractProcessor>
     */
    public function resolve(Node $node): string
    {
        return match ($node::class) {
            Root::class       => RootProcessor::class,
            Condition::class  => ConditionProcessor::class,
            Collection::class => CollectionProcessor::class,
            Value::class      => ValueProcessor::class,
            Context::class    => ContextProcessor::class,

            default => throw new InvalidArgumentException(
                sprintf('Unknown operator type "%s"', $node::class),
            )
        };
    }
}
