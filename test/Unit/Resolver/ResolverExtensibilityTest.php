<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Resolver;

use PHPUnit\Framework\TestCase;
use TheChoice\Builder\BuilderInterface;
use TheChoice\Context\ContextInterface;
use TheChoice\Node\Node;
use TheChoice\Node\Value;
use TheChoice\NodeFactory\NodeFactoryInterface;
use TheChoice\NodeFactory\NodeFactoryResolver;
use TheChoice\Operator\OperatorInterface;
use TheChoice\Operator\OperatorResolver;
use TheChoice\Processor\AbstractProcessor;
use TheChoice\Processor\ProcessorResolver;

final class ResolverExtensibilityTest extends TestCase
{
    public function testOperatorResolverCanRegisterCustomOperator(): void
    {
        $resolver = new OperatorResolver();
        $resolver->register('alwaysTrue', AlwaysTrueOperator::class);

        self::assertSame(AlwaysTrueOperator::class, $resolver->resolve('alwaysTrue'));
    }

    public function testNodeFactoryResolverCanRegisterCustomFactory(): void
    {
        $resolver = new NodeFactoryResolver();
        $resolver->register('customNode', CustomNodeFactory::class);

        self::assertSame(CustomNodeFactory::class, $resolver->resolve('customNode'));
    }

    public function testProcessorResolverCanRegisterCustomProcessor(): void
    {
        $resolver = new ProcessorResolver();
        $resolver->register(CustomNode::class, CustomProcessor::class);

        $node = new CustomNode();

        self::assertSame(CustomProcessor::class, $resolver->resolve($node));
    }
}

final class AlwaysTrueOperator implements OperatorInterface
{
    private mixed $value = null;

    public static function getOperatorName(): string
    {
        return 'alwaysTrue';
    }

    public function setValue(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function assert(ContextInterface $context): bool
    {
        return true;
    }
}

final class CustomNodeFactory implements NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure): Node
    {
        return new Value('custom');
    }
}

final class CustomNode implements Node
{
    public static function getNodeName(): string
    {
        return 'custom';
    }
}

final class CustomProcessor extends AbstractProcessor
{
    public function process(Node $node): mixed
    {
        return 'processed';
    }
}
