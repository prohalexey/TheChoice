<?php

declare(strict_types=1);

namespace TheChoice\Node;

use TheChoice\Exception\InvalidArgumentException;

class Root extends AbstractNode
{
    protected $storage = [];
    protected $rules;
    protected $result;

    public static function getNodeName(): string
    {
        return 'root';
    }

    public function getRules(): Node
    {
        return $this->rules;
    }

    public function setRules($node): self
    {
        $this->rules = $node;
        return $this;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function hasResult(): bool
    {
        return null !== $this->result;
    }

    public function setResult($result): self
    {
        $this->result = $result;
        return $this;
    }

    public function getStorage(): array
    {
        return $this->storage;
    }

    public function getStorageValue($name)
    {
        return $this->storage[$name] ?? null;
    }

    public function setGlobal($key, $value)
    {
        if (!preg_match('#[a-z][a-z0-9_]+#i', $key)) {
            throw new InvalidArgumentException(
                'The key in "storage" property of node type "Root" must be string(format: #[a-z][a-z0-9_]+#i)'
            );
        }

        if ($key === 'context') {
            throw new InvalidArgumentException(
                'The key "context" for root context is reserved and cannot be used'
            );
        }

        return $this->storage[$key] = $value;
    }
}