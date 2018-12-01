<?php

namespace TheChoice\Node;

final class Tree
{
    private $_storage = [];
    private $_description = '';
    private $_nodes;

    public function getNodes()
    {
        return $this->_nodes;
    }

    public function setNodes($nodes)
    {
        $this->_nodes = $nodes;
        return $this;
    }

    public function setDescription(string $description)
    {
        $this->_description = $description;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->_description;
    }

    public function getStorage()
    {
        return $this->_storage;
    }

    public function getGlobal($name)
    {
        return $this->_storage[$name] ?? null;
    }

    public function setGlobal($key, $value)
    {
        if (!preg_match('#[a-z][a-z0-9_]+#i', $key)) {
            throw new \InvalidArgumentException(
                'The key in "storage" property of node type "Tree" must be string(format: #[a-z][a-z0-9_]+#i)'
            );
        }

        if ($key === 'context') {
            throw new \InvalidArgumentException(
                'The key "context" for tree context is reserved and can\'t be used'
            );
        }

        return $this->_storage[$key] = $value;
    }
}