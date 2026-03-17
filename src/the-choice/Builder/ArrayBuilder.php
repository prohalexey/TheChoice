<?php

declare(strict_types=1);

namespace TheChoice\Builder;

use Psr\Container\ContainerInterface;
use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Exception\LogicException;
use TheChoice\Node\Node;
use TheChoice\Node\Root;
use TheChoice\NodeFactory\NodeFactoryInterface;
use TheChoice\NodeFactory\NodeFactoryResolverInterface;

class ArrayBuilder implements BuilderInterface
{
    protected Root $rootNode;

    protected int $nodesCount = 0;

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function build(array &$structure): Node
    {
        if (!array_key_exists('node', $structure)) {
            throw new InvalidArgumentException('The "node" property is absent!');
        }

        $isTopLevel = (0 === $this->nodesCount);
        $this->nodesCount++;

        // Workaround for short syntax if the root node is omitted
        if ($isTopLevel && $structure['node'] !== Root::getNodeName()) {
            $structure = [
                'node'  => Root::getNodeName(),
                'rules' => $structure,
            ];

            $this->nodesCount--;

            return $this->build($structure);
        }

        if (!is_string($structure['node'])) {
            throw new InvalidArgumentException('Node type must be a string');
        }

        if (!$isTopLevel && $structure['node'] === Root::getNodeName()) {
            throw new LogicException('The node "Root" cannot be not root node!');
        }

        /** @var NodeFactoryResolverInterface $nodeFactoryResolver */
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

    public function resetNodesCount(): void
    {
        $this->nodesCount = 0;
    }
}
