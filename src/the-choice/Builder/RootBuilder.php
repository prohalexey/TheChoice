<?php

declare(strict_types=1);

namespace TheChoice\Builder;

use TheChoice\Exception\LogicException;
use TheChoice\Node\AbstractChildNode;
use TheChoice\Node\Collection;
use TheChoice\Node\Condition;
use TheChoice\Node\Node;
use TheChoice\Node\Root;
use TheChoice\Node\SwitchNode;

final class RootBuilder implements NodeBuilderInterface
{
    private ?NodeBuilderInterface $rulesBuilder = null;

    /** @var array<string, mixed> */
    private array $storage = [];

    private ?string $description = null;

    public function rules(NodeBuilderInterface $builder): self
    {
        $this->rulesBuilder = $builder;

        return $this;
    }

    /**
     * Defines named storage variables accessible in modifier expressions (e.g. `$myVar`).
     *
     * @param array<string, mixed> $storage
     */
    public function storage(array $storage): self
    {
        $this->storage = $storage;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function build(): Root
    {
        if (null === $this->rulesBuilder) {
            throw new LogicException('Root "rules" node must be provided before calling build()');
        }

        $root = new Root();

        foreach ($this->storage as $key => $value) {
            $root->setGlobal($key, $value);
        }

        if (null !== $this->description) {
            $root->setDescription($this->description);
        }

        $rules = $this->rulesBuilder->build();
        $root->setRules($rules);

        // Propagate the Root reference to every AbstractChildNode in the tree
        // so that modifiers, stoppable contexts, and SwitchProcessor can access Root.
        $this->propagateRoot($root, $rules);

        return $root;
    }

    /**
     * Recursively walks the node tree and calls setRoot() on every AbstractChildNode.
     */
    private function propagateRoot(Root $root, Node $node): void
    {
        if ($node instanceof AbstractChildNode) {
            $node->setRoot($root);
        }

        if ($node instanceof Condition) {
            $this->propagateRoot($root, $node->getIfNode());
            $this->propagateRoot($root, $node->getThenNode());

            $elseNode = $node->getElseNode();
            if (null !== $elseNode) {
                $this->propagateRoot($root, $elseNode);
            }

            return;
        }

        if ($node instanceof Collection) {
            foreach ($node->all() as $child) {
                $this->propagateRoot($root, $child);
            }

            return;
        }

        if ($node instanceof SwitchNode) {
            foreach ($node->getCases() as $case) {
                $this->propagateRoot($root, $case->getThenNode());
            }

            $defaultNode = $node->getDefaultNode();
            if (null !== $defaultNode) {
                $this->propagateRoot($root, $defaultNode);
            }

            return;
        }
    }
}
