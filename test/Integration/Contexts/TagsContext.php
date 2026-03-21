<?php

declare(strict_types=1);

namespace TheChoice\Tests\Integration\Contexts;

use TheChoice\Context\ContextInterface;

class TagsContext implements ContextInterface
{
    /**
     * @return array<string>
     */
    public function getValue(): array
    {
        return ['php', 'laravel', 'symfony'];
    }
}
