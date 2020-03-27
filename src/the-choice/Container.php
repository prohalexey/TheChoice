<?php

declare(strict_types=1);

namespace TheChoice;

use Psr\Container\ContainerInterface;

use TheChoice\Builder\ArrayBuilder;
use TheChoice\Builder\JsonBuilder;
use TheChoice\Builder\YamlBuilder;

use TheChoice\Context\ContextFactoryInterface;
use TheChoice\Context\ContextFactory;

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
use TheChoice\Operator\StringContain;
use TheChoice\Operator\StringNotContain;
use TheChoice\Operator\OperatorResolverInterface;

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
    protected $services = [];

    protected $classMap;

    public $builders = [
        ArrayBuilder::class,
        JsonBuilder::class,
        YamlBuilder::class,
    ];

    public $operators = [
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

    public $nodeFactories = [
        NodeConditionFactory::class,
        NodeContextFactory::class,
        NodeCollectionFactory::class,
        NodeRootFactory::class,
        NodeValueFactory::class,
    ];

    public $processors = [
        CollectionProcessor::class,
        ContextProcessor::class,
        ConditionProcessor::class,
        RootProcessor::class,
        ValueProcessor::class,
    ];

    public $interfaces = [
        NodeFactoryResolverInterface::class,
        OperatorResolverInterface::class,
        ProcessorResolverInterface::class,
        ContextFactoryInterface::class
    ];

    protected $contexts = [];

    public function __construct(array $contexts)
    {
        $this->contexts = $contexts;

        $this->classMap = array_merge(
            $this->builders,
            $this->operators,
            $this->nodeFactories,
            $this->processors,
            $this->interfaces
        );
    }

    public function get($id)
    {
        if ($id === NodeFactoryResolverInterface::class) {
            if (!array_key_exists(NodeFactoryResolverInterface::class, $this->services)) {
                $this->services[NodeFactoryResolverInterface::class] = new NodeFactoryResolver();

            }
            return $this->services[NodeFactoryResolverInterface::class];
        }

        if ($id === OperatorResolverInterface::class) {
            if (!array_key_exists(OperatorResolverInterface::class, $this->services)) {
                $this->services[OperatorResolverInterface::class] = new OperatorResolver();
            }
            return $this->services[OperatorResolverInterface::class];
        }

        if ($id === ProcessorResolverInterface::class) {
            if (!array_key_exists(ProcessorResolverInterface::class, $this->services)) {
                $this->services[ProcessorResolverInterface::class] = new ProcessorResolver();
            }
            return $this->services[ProcessorResolverInterface::class];
        }

        if (in_array($id, $this->nodeFactories, true)) {
            if (!array_key_exists($id, $this->services)) {
                $this->services[$id] = new $id;
            }
            return $this->services[$id];
        }

        if (in_array($id, $this->builders, true)) {
            return new $id($this);
        }

        if (in_array($id, $this->operators, true)) {
            return new $id;
        }

        if (in_array($id, $this->processors, true)) {
            /** @var AbstractProcessor $processor */
            $processor = new $id;
            $processor->setContainer($this);

            if ($id === ContextProcessor::class) {
                /** @var ContextProcessor $processor */
                $processor->setContextFactory($this->get(ContextFactoryInterface::class));
            }

            return $processor;
        }

        if ($id === ContextFactoryInterface::class) {
            $contextFactory = new ContextFactory($this->contexts);
            $contextFactory->setContainer($this);
            return $contextFactory;
        }

        throw new ContainerNotFoundException(sprintf('There is no configuration for "%s" item in the container', $id));
    }

    public function has($id)
    {
        return in_array($id, $this->classMap);
    }
}