<?php

declare(strict_types=1);

namespace TheChoice\Node;

class Value extends AbstractChildNode
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public static function getNodeName(): string
    {
        return 'value';
    }

    public function getValue()
    {
        return $this->value;
    }
}