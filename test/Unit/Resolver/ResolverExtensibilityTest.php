<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Resolver;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use TheChoice\Builder\BuilderInterface;
use TheChoice\Context\ContextInterface;
use TheChoice\Node\AbstractChildNode;
use TheChoice\Node\Context;
use TheChoice\Node\HasChildNodes;
use TheChoice\Node\Node;
use TheChoice\Node\Root;
use TheChoice\Node\Value;
use TheChoice\NodeFactory\NodeFactoryInterface;
use TheChoice\NodeFactory\NodeFactoryResolver;
use TheChoice\Operator\OperatorInterface;
use TheChoice\Operator\OperatorResolver;
use TheChoice\Processor\AbstractProcessor;
use TheChoice\Processor\ProcessorResolver;
use TheChoice\Processor\RootProcessor;

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

    /**
     * Verifies that when a custom node implements HasChildNodes,
     * RootProcessor::iterateNodes() recurses into its children.
     * This ensures that per-evaluation caches (e.g. ContextProcessor)
     * are properly flushed for nodes nested inside custom composites.
     */
    public function testIterateNodesRecursesIntoHasChildNodesChildren(): void
    {
        $root = new Root();

        $contextNode = new Context();
        $contextNode->setContextName('test');
        $contextNode->setRoot($root);

        $composite = new CustomCompositeNode($contextNode);
        $composite->setRoot($root);

        $root->setRules($composite);

        $processor = new RootProcessor();

        $reflection = new ReflectionClass($processor);
        $method = $reflection->getMethod('iterateNodes');
        $method->setAccessible(true);

        /** @var array<Node> $nodes */
        $nodes = iterator_to_array($method->invoke($processor, $root), false);

        // Root → CustomCompositeNode → Context (via HasChildNodes)
        self::assertCount(3, $nodes);

        $nodeClasses = array_map(static fn (Node $n): string => $n::class, $nodes);
        self::assertContains(Root::class, $nodeClasses);
        self::assertContains(CustomCompositeNode::class, $nodeClasses);
        self::assertContains(Context::class, $nodeClasses);
    }

    public function testNodeWithoutHasChildNodesDoesNotCauseRecursion(): void
    {
        $root = new Root();
        $value = new Value(1);
        $value->setRoot($root);

        $root->setRules($value);

        $processor = new RootProcessor();

        $reflection = new ReflectionClass($processor);
        $method = $reflection->getMethod('iterateNodes');
        $method->setAccessible(true);

        /** @var array<Node> $nodes */
        $nodes = iterator_to_array($method->invoke($processor, $root), false);

        // Only Root and Value — Value has no children
        self::assertCount(2, $nodes);
    }
}

// ─── Stubs ────────────────────────────────────────────────────────────────────

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

/**
 * A custom composite node that wraps a single child and implements HasChildNodes
 * so RootProcessor can discover the child during its iterateNodes() pass.
 */
final class CustomCompositeNode extends AbstractChildNode implements HasChildNodes
{
    public function __construct(private readonly Node $child)
    {
    }

    public function getChildNodes(): iterable
    {
        yield $this->child;
    }

    public static function getNodeName(): string
    {
        return 'customComposite';
    }
}
