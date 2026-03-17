<?php

declare(strict_types=1);

namespace TheChoice;

use Psr\Container\ContainerInterface;
use TheChoice\Builder\ArrayBuilder;
use TheChoice\Builder\JsonBuilder;
use TheChoice\Builder\YamlBuilder;
use TheChoice\Context\ContextFactory;
use TheChoice\Context\ContextFactoryInterface;
use TheChoice\Exception\ContainerNotFoundException;
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
use TheChoice\Operator\OperatorResolver;
use TheChoice\Operator\OperatorResolverInterface;
use TheChoice\Operator\StringContain;
use TheChoice\Operator\StringNotContain;
use TheChoice\Processor\AbstractProcessor;
use TheChoice\Processor\CollectionProcessor;
use TheChoice\Processor\ConditionProcessor;
use TheChoice\Processor\ContextProcessor;
use TheChoice\Processor\ProcessorResolver;
use TheChoice\Processor\ProcessorResolverInterface;
use TheChoice\Processor\RootProcessor;
use TheChoice\Processor\ValueProcessor;

class Container implements ContainerInterface
{
    /** @var array<class-string> */
    private array $builders = [
        ArrayBuilder::class,
        JsonBuilder::class,
        YamlBuilder::class,
    ];

    /** @var array<class-string> */
    private array $operators = [
        ArrayContain::class,
        ArrayNotContain::class,
        Equal::class,
        GreaterThan::class,
        GreaterThanOrEqual::class,
        LowerThan::class,
        LowerThanOrEqual::class,
        NotEqual::class,
        NumericInRange::class,
        StringContain::class,
        StringNotContain::class,
    ];

    /** @var array<class-string> */
    private array $nodeFactories = [
        NodeConditionFactory::class,
        NodeContextFactory::class,
        NodeCollectionFactory::class,
        NodeRootFactory::class,
        NodeValueFactory::class,
    ];

    /** @var array<class-string> */
    private array $processors = [
        CollectionProcessor::class,
        ContextProcessor::class,
        ConditionProcessor::class,
        RootProcessor::class,
        ValueProcessor::class,
    ];

    /** @var array<class-string> */
    private array $interfaces = [
        NodeFactoryResolverInterface::class,
        OperatorResolverInterface::class,
        ProcessorResolverInterface::class,
        ContextFactoryInterface::class,
    ];

    /** @var array<string, object> */
    protected array $services = [];

    /** @var array<string, array{shared: bool, factory: callable(): object}> */
    private array $definitions = [];

    protected array $contexts;

    public function __construct(array $contexts)
    {
        $this->contexts = $contexts;

        $this->registerDefaultDefinitions();
    }

    /**
     * @throws ContainerNotFoundException
     */
    public function get(string $id): object
    {
        if (array_key_exists($id, $this->definitions)) {
            return $this->resolveDefinition($id);
        }

        if (in_array($id, $this->nodeFactories, true)) {
            return $this->getOrCreateShared($id, static fn (): object => new $id());
        }

        if (in_array($id, $this->builders, true)) {
            return new $id($this);
        }

        if (in_array($id, $this->operators, true)) {
            return new $id();
        }

        if (in_array($id, $this->processors, true)) {
            assert(is_a($id, AbstractProcessor::class, true));

            return $this->createProcessor($id);
        }

        throw new ContainerNotFoundException(sprintf('There is no configuration for "%s" item in the container', $id));
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->definitions)
            || in_array($id, $this->builders, true)
            || in_array($id, $this->operators, true)
            || in_array($id, $this->nodeFactories, true)
            || in_array($id, $this->processors, true)
            || in_array($id, $this->interfaces, true);
    }

    /**
     * Registers a shared service definition (singleton-like lifecycle).
     *
     * @param callable(): object $factory
     */
    public function registerShared(string $id, callable $factory): self
    {
        $this->registerDefinition($id, $factory, true);

        return $this;
    }

    /**
     * Registers a transient service definition (new instance per get()).
     *
     * @param callable(): object $factory
     */
    public function registerTransient(string $id, callable $factory): self
    {
        $this->registerDefinition($id, $factory, false);

        return $this;
    }

    private function registerDefaultDefinitions(): void
    {
        $this->registerDefinition(
            NodeFactoryResolverInterface::class,
            static fn (): object => new NodeFactoryResolver(),
            true,
        );

        $this->registerDefinition(
            OperatorResolverInterface::class,
            static fn (): object => new OperatorResolver(),
            true,
        );

        $this->registerDefinition(
            ProcessorResolverInterface::class,
            static fn (): object => new ProcessorResolver(),
            true,
        );

        $this->registerDefinition(
            ContextFactoryInterface::class,
            function (): object {
                $contextFactory = new ContextFactory($this->contexts);
                $contextFactory->setContainer($this);

                return $contextFactory;
            },
            false,
        );
    }

    /**
     * @param callable(): object $factory
     */
    private function registerDefinition(string $id, callable $factory, bool $shared): void
    {
        // If a shared service was already resolved under this id, drop it so
        // the new definition is applied on subsequent get() calls.
        unset($this->services[$id]);

        $this->definitions[$id] = [
            'shared'  => $shared,
            'factory' => $factory,
        ];
    }

    private function resolveDefinition(string $id): object
    {
        $definition = $this->definitions[$id];
        if ($definition['shared']) {
            return $this->getOrCreateShared($id, $definition['factory']);
        }

        $factory = $definition['factory'];

        return $factory();
    }

    /**
     * @param callable(): object $factory
     */
    private function getOrCreateShared(string $id, callable $factory): object
    {
        if (!array_key_exists($id, $this->services)) {
            $this->services[$id] = $factory();
        }

        return $this->services[$id];
    }

    /**
     * @param class-string<AbstractProcessor> $id
     */
    private function createProcessor(string $id): AbstractProcessor
    {
        /** @var AbstractProcessor $processor */
        $processor = new $id();
        $processor->setContainer($this);

        if (ContextProcessor::class === $id) {
            /** @var ContextProcessor $processor */
            $contextFactory = $this->get(ContextFactoryInterface::class);
            if ($contextFactory instanceof ContextFactoryInterface) {
                $processor->setContextFactory($contextFactory);
            }
        }

        return $processor;
    }
}
