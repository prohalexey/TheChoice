<?php

declare(strict_types=1);

namespace TheChoice\Node;

abstract class AbstractNode implements Node
{
    protected $description;

    abstract public static function getNodeName(): string;

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}