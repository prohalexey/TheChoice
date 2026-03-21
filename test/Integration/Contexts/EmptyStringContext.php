<?php

declare(strict_types=1);

namespace TheChoice\Tests\Integration\Contexts;

use TheChoice\Context\ContextInterface;

class EmptyStringContext implements ContextInterface
{
    public function getValue(): string
    {
        return '';
    }
}
