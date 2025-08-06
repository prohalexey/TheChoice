<?php

declare(strict_types=1);

namespace TheChoice\Node;

use TheChoice\Exception\InvalidArgumentException;

class Root extends AbstractNode
{
    protected array $storage = [];

    protected Node $rules;

    protected mixed $result = null;

    public static function getNodeName(): string
    {
        return 'root';
    }

    public function getRules(): Node
    {
        return $this->rules;
    }

    public function setRules(Node $node): self
    {
        $this->rules = $node;

        return $this;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function hasResult(): bool
    {
        return null !== $this->result;
    }

    public function setResult(mixed $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function getStorage(): array
    {
        return $this->storage;
    }

    public function getStorageValue(string $name): mixed
    {
        return $this->storage[$name] ?? null;
    }

    public function setGlobal(string $key, mixed $value): mixed
    {
        if (!preg_match('#[a-z][a-z0-9_]+#i', $key)) {
            throw new InvalidArgumentException(
                'The key in "storage" property of node type "Root" must be string(format: #[a-z][a-z0-9_]+#i)',
            );
        }

        if ('context' === $key) {
            throw new InvalidArgumentException(
                'The key "context" for root context is reserved and cannot be used',
            );
        }

        return $this->storage[$key] = $value;
    }
}
