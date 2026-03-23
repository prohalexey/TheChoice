<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;
use TheChoice\Builder\JsonBuilder;
use TheChoice\Container;
use TheChoice\Context\ContextFactoryInterface;
use TheChoice\Exception\ContainerNotFoundException;
use TheChoice\NodeFactory\NodeConditionFactory;
use TheChoice\NodeFactory\NodeFactoryResolverInterface;
use TheChoice\Operator\Equal;
use TheChoice\Operator\OperatorResolverInterface;
use TheChoice\Processor\ContextProcessor;
use TheChoice\Processor\ProcessorResolverInterface;
use TheChoice\Processor\RootProcessor;
use TheChoice\Processor\SwitchProcessor;

final class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container([]);
    }

    public function testSharedResolverServices(): void
    {
        $first = $this->container->get(NodeFactoryResolverInterface::class);
        $second = $this->container->get(NodeFactoryResolverInterface::class);
        self::assertSame($first, $second);

        $first = $this->container->get(OperatorResolverInterface::class);
        $second = $this->container->get(OperatorResolverInterface::class);
        self::assertSame($first, $second);

        $first = $this->container->get(ProcessorResolverInterface::class);
        $second = $this->container->get(ProcessorResolverInterface::class);
        self::assertSame($first, $second);
    }

    public function testContextFactoryIsTransient(): void
    {
        $first = $this->container->get(ContextFactoryInterface::class);
        $second = $this->container->get(ContextFactoryInterface::class);

        self::assertNotSame($first, $second);
    }

    public function testNodeFactoriesAreShared(): void
    {
        $first = $this->container->get(NodeConditionFactory::class);
        $second = $this->container->get(NodeConditionFactory::class);

        self::assertSame($first, $second);
    }

    public function testBuildersOperatorsAndProcessorsAreTransient(): void
    {
        self::assertNotSame(
            $this->container->get(JsonBuilder::class),
            $this->container->get(JsonBuilder::class),
        );

        self::assertNotSame(
            $this->container->get(Equal::class),
            $this->container->get(Equal::class),
        );

        self::assertNotSame(
            $this->container->get(RootProcessor::class),
            $this->container->get(RootProcessor::class),
        );
    }

    public function testContextProcessorGetsContextFactory(): void
    {
        $processor = $this->container->get(ContextProcessor::class);

        $reflection = new ReflectionClass($processor);
        $property = $reflection->getProperty('contextFactory');
        $property->setAccessible(true);

        self::assertNotNull($property->getValue($processor));
    }

    public function testHasAndUnknownServiceBehavior(): void
    {
        self::assertTrue($this->container->has(JsonBuilder::class));
        self::assertTrue($this->container->has(NodeFactoryResolverInterface::class));
        self::assertFalse($this->container->has('Unknown\Service'));

        $this->expectException(ContainerNotFoundException::class);
        $this->container->get('Unknown\Service');
    }

    public function testRegisterSharedProvidesSameInstance(): void
    {
        $this->container->registerShared('custom.shared', static fn (): object => new stdClass());

        self::assertTrue($this->container->has('custom.shared'));
        self::assertSame(
            $this->container->get('custom.shared'),
            $this->container->get('custom.shared'),
        );
    }

    public function testRegisterTransientProvidesNewInstance(): void
    {
        $this->container->registerTransient('custom.transient', static fn (): object => new stdClass());

        self::assertTrue($this->container->has('custom.transient'));
        self::assertNotSame(
            $this->container->get('custom.transient'),
            $this->container->get('custom.transient'),
        );
    }

    public function testCanOverrideExistingSharedDefinition(): void
    {
        $defaultResolver = $this->container->get(NodeFactoryResolverInterface::class);

        $customResolver = new class {
        };

        $this->container->registerShared(
            NodeFactoryResolverInterface::class,
            static fn (): object => $customResolver,
        );

        $resolved = $this->container->get(NodeFactoryResolverInterface::class);

        self::assertNotSame($defaultResolver, $resolved);
        self::assertSame($customResolver, $resolved);
    }

    public function testSwitchProcessorGetsContextFactory(): void
    {
        $processor = $this->container->get(SwitchProcessor::class);

        $reflection = new ReflectionClass($processor);
        $property = $reflection->getProperty('contextFactory');
        $property->setAccessible(true);

        self::assertNotNull($property->getValue($processor));
    }

    public function testHasReturnsTrueForAllDefaultInterfaces(): void
    {
        self::assertTrue($this->container->has(NodeFactoryResolverInterface::class));
        self::assertTrue($this->container->has(OperatorResolverInterface::class));
        self::assertTrue($this->container->has(ProcessorResolverInterface::class));
        self::assertTrue($this->container->has(ContextFactoryInterface::class));
    }

    public function testGetWorksForAllDefaultInterfaces(): void
    {
        // has() returns true → get() must not throw
        self::assertNotNull($this->container->get(NodeFactoryResolverInterface::class));
        self::assertNotNull($this->container->get(OperatorResolverInterface::class));
        self::assertNotNull($this->container->get(ProcessorResolverInterface::class));
        self::assertNotNull($this->container->get(ContextFactoryInterface::class));
    }
}
