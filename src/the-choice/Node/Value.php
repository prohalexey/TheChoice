<?php

declare(strict_types=1);

namespace TheChoice\Node;

class Value extends AbstractChildNode
{
    protected mixed $value;

    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    public static function getNodeName(): string
    {
        return 'value';
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
