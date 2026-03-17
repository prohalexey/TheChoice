<?php

declare(strict_types=1);

namespace TheChoice\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use TheChoice\Builder\JsonBuilder;
use TheChoice\Context\ContextFactory;
use TheChoice\Context\ContextFactoryInterface;
use TheChoice\Context\ContextInterface;
use TheChoice\NodeFactory\NodeCollectionFactory;
use TheChoice\NodeFactory\NodeConditionFactory;
use TheChoice\NodeFactory\NodeContextFactory;
use TheChoice\NodeFactory\NodeFactoryResolver;
use TheChoice\NodeFactory\NodeFactoryResolverInterface;
use TheChoice\NodeFactory\NodeRootFactory;
use TheChoice\NodeFactory\NodeValueFactory;
use TheChoice\Operator\ArrayContain;
use TheChoice\Operator\ArrayNotContain;
use TheChoice\Operator\Equal;
use TheChoice\Operator\GreaterThan;
use TheChoice\Operator\GreaterThanOrEqual;
use TheChoice\Operator\LowerThan;
use TheChoice\Operator\LowerThanOrEqual;
use TheChoice\Operator\NotEqual;
use TheChoice\Operator\NumericInRange;
use TheChoice\Operator\OperatorInterface;
use TheChoice\Operator\OperatorResolver;
use TheChoice\Operator\OperatorResolverInterface;
use TheChoice\Operator\StringContain;
use TheChoice\Operator\StringNotContain;
use TheChoice\Processor\CollectionProcessor;
use TheChoice\Processor\ConditionProcessor;
use TheChoice\Processor\ContextProcessor;
use TheChoice\Processor\ProcessorResolver;
use TheChoice\Processor\ProcessorResolverInterface;
use TheChoice\Processor\RootProcessor;
use TheChoice\Processor\ValueProcessor;

final class SymfonyContainerIntegrationTest extends TestCase
{
    public function testSymfonyContainerSupportsCustomOperatorRegistration(): void
    {
        if (!class_exists(ContainerBuilder::class)) {
            self::markTestSkipped('symfony/dependency-injection is not installed');
        }

        $container = new ContainerBuilder();

        $container->register(SymfonyTestStringContext::class, SymfonyTestStringContext::class)->setPublic(true);

        $container
            ->register(ContextFactoryInterface::class, ContextFactory::class)
            ->setArguments([['symfonyString' => SymfonyTestStringContext::class]])
            ->addMethodCall('setContainer', [new Reference('service_container')])
            ->setPublic(true)
        ;

        $container
            ->register(OperatorResolverInterface::class, OperatorResolver::class)
            ->addMethodCall('register', ['startsWith', StartsWithOperator::class])
            ->setPublic(true)
        ;

        $container->register(NodeFactoryResolverInterface::class, NodeFactoryResolver::class)->setPublic(true);
        $container->register(ProcessorResolverInterface::class, ProcessorResolver::class)->setPublic(true);

        $container->register(NodeConditionFactory::class, NodeConditionFactory::class)->setPublic(true);
        $container->register(NodeContextFactory::class, NodeContextFactory::class)->setPublic(true);
        $container->register(NodeCollectionFactory::class, NodeCollectionFactory::class)->setPublic(true);
        $container->register(NodeRootFactory::class, NodeRootFactory::class)->setPublic(true);
        $container->register(NodeValueFactory::class, NodeValueFactory::class)->setPublic(true);

        $container->register(ArrayContain::class, ArrayContain::class)->setPublic(true);
        $container->register(ArrayNotContain::class, ArrayNotContain::class)->setPublic(true);
        $container->register(Equal::class, Equal::class)->setPublic(true);
        $container->register(GreaterThan::class, GreaterThan::class)->setPublic(true);
        $container->register(GreaterThanOrEqual::class, GreaterThanOrEqual::class)->setPublic(true);
        $container->register(LowerThan::class, LowerThan::class)->setPublic(true);
        $container->register(LowerThanOrEqual::class, LowerThanOrEqual::class)->setPublic(true);
        $container->register(NotEqual::class, NotEqual::class)->setPublic(true);
        $container->register(NumericInRange::class, NumericInRange::class)->setPublic(true);
        $container->register(StringContain::class, StringContain::class)->setPublic(true);
        $container->register(StringNotContain::class, StringNotContain::class)->setPublic(true);
        $container->register(StartsWithOperator::class, StartsWithOperator::class)->setPublic(true);

        $container
            ->register(CollectionProcessor::class, CollectionProcessor::class)
            ->addMethodCall('setContainer', [new Reference('service_container')])
            ->setPublic(true)
        ;

        $container
            ->register(ConditionProcessor::class, ConditionProcessor::class)
            ->addMethodCall('setContainer', [new Reference('service_container')])
            ->setPublic(true)
        ;

        $container
            ->register(ContextProcessor::class, ContextProcessor::class)
            ->addMethodCall('setContainer', [new Reference('service_container')])
            ->addMethodCall('setContextFactory', [new Reference(ContextFactoryInterface::class)])
            ->setPublic(true)
        ;

        $container
            ->register(ValueProcessor::class, ValueProcessor::class)
            ->addMethodCall('setContainer', [new Reference('service_container')])
            ->setPublic(true)
        ;

        $container
            ->register(RootProcessor::class, RootProcessor::class)
            ->addMethodCall('setContainer', [new Reference('service_container')])
            ->setPublic(true)
        ;

        $container
            ->register(JsonBuilder::class, JsonBuilder::class)
            ->setArguments([new Reference('service_container')])
            ->setPublic(true)
        ;

        $container->compile();

        /** @var JsonBuilder $builder */
        $builder = $container->get(JsonBuilder::class);

        $rulesJson = json_encode([
            'node'     => 'context',
            'context'  => 'symfonyString',
            'operator' => 'startsWith',
            'value'    => 'sym',
        ]);

        self::assertIsString($rulesJson);

        $rootNode = $builder->parse($rulesJson);

        /** @var RootProcessor $rootProcessor */
        $rootProcessor = $container->get(RootProcessor::class);

        self::assertTrue($rootProcessor->process($rootNode));
    }
}

final class SymfonyTestStringContext implements ContextInterface
{
    public function getValue(): string
    {
        return 'symfony';
    }
}

final class StartsWithOperator implements OperatorInterface
{
    private mixed $value = null;

    public static function getOperatorName(): string
    {
        return 'startsWith';
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
        $needle = (string)$this->value;
        $haystack = (string)$context->getValue();

        return '' === $needle || str_starts_with($haystack, $needle);
    }
}
