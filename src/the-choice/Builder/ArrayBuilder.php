<?php

declare(strict_types=1);

namespace TheChoice\Builder;

use Psr\Container\ContainerInterface;

use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Exception\LogicException;
use TheChoice\Node\Root;
use TheChoice\NodeFactory\NodeFactoryInterface;
use TheChoice\NodeFactory\NodeFactoryResolverInterface;

class ArrayBuilder implements BuilderInterface
{
    protected $rootNode;

    protected $nodesCount = 0;

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function build(&$structure)
    {
        if (!array_key_exists('node', $structure)) {
            throw new InvalidArgumentException('The "node" property is absent!');
        }

        $this->nodesCount++;

        /**
         * Workaround for short syntax if the root node is omitted
         */
        if ($this->nodesCount === 1 && $structure['node'] !== Root::getNodeName()) {
            $structure = [
                'node' => Root::getNodeName(),
                'rules' => $structure
            ];

            $this->nodesCount--;

            return $this->build($structure);
        }

        if ($this->nodesCount !== 1 && $structure['node'] === Root::getNodeName()) {
            throw new LogicException('The node "Root" cannot be not root node!');
        }

        $nodeFactoryResolver = $this->container->get(NodeFactoryResolverInterface::class);
        $nodeFactoryType = $nodeFactoryResolver->resolve($structure['node']);

        /** @var NodeFactoryInterface $nodeFactory */
        $nodeFactory = $this->container->get($nodeFactoryType);
        return $nodeFactory->build($this, $structure);
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function setRoot(Root $rootNode): BuilderInterface
    {
        $this->rootNode = $rootNode;
        return $this;
    }

    public function getRoot(): Root
    {
        return $this->rootNode;
    }
}
