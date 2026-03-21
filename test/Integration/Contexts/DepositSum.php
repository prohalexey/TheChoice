<?php

declare(strict_types=1);

namespace TheChoice\Tests\Integration\Contexts;

use TheChoice\Context\ContextInterface;

class DepositSum implements ContextInterface
{
    public function getValue(): int
    {
        return 6000;
    }
}
