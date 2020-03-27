<?php

declare(strict_types=1);

namespace TheChoice\Operator;

use TheChoice\Context\ContextInterface;

interface OperatorInterface
{
    public static function getOperatorName(): string;

    public function setValue($value);

    public function getValue();

    public function assert(ContextInterface $context): bool;
}