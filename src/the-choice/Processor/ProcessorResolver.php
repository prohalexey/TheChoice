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
    /** @var array<class-string<Node>, class-string<AbstractProcessor>> */
    private array $processorMap = [];

    /**
     * @param array<class-string<Node>, class-string<AbstractProcessor>> $processorMap
     */
    public function __construct(array $processorMap = [])
    {
        foreach (self::getDefaultMap() as $nodeClass => $processorClass) {
            $this->register($nodeClass, $processorClass);
        }

        foreach ($processorMap as $nodeClass => $processorClass) {
            $this->register($nodeClass, $processorClass);
        }
    }

    /**
     * @param class-string<Node>              $nodeClass
     * @param class-string<AbstractProcessor> $processorClass
     */
    public function register(string $nodeClass, string $processorClass): self
    {
        if (!is_a($nodeClass, Node::class, true)) {
            throw new InvalidArgumentException(
                sprintf('Node class "%s" must implement %s', $nodeClass, Node::class),
            );
        }

        if (!is_a($processorClass, AbstractProcessor::class, true)) {
            throw new InvalidArgumentException(
                sprintf('Processor class "%s" must extend %s', $processorClass, AbstractProcessor::class),
            );
        }

        $this->processorMap[$nodeClass] = $processorClass;

        return $this;
    }

    /**
     * @return class-string<AbstractProcessor>
     */
    public function resolve(Node $node): string
    {
        $nodeClass = $node::class;

        if (array_key_exists($nodeClass, $this->processorMap)) {
            return $this->processorMap[$nodeClass];
        }

        foreach ($this->processorMap as $mappedNodeClass => $processorClass) {
            if ($node instanceof $mappedNodeClass) {
                return $processorClass;
            }
        }

        throw new InvalidArgumentException(
            sprintf('Unknown node type "%s"', $nodeClass),
        );
    }

    /**
     * @return array<class-string<Node>, class-string<AbstractProcessor>>
     */
    private static function getDefaultMap(): array
    {
        return [
            Root::class       => RootProcessor::class,
            Condition::class  => ConditionProcessor::class,
            Collection::class => CollectionProcessor::class,
            Value::class      => ValueProcessor::class,
            Context::class    => ContextProcessor::class,
        ];
    }
}
