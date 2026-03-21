<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Registry;

use PHPUnit\Framework\TestCase;
use TheChoice\Node\Value;
use TheChoice\Registry\RuleEntry;

final class RuleEntryTest extends TestCase
{
    public function testConstructorSetsAllProperties(): void
    {
        $node = new Value(42);

        $entry = new RuleEntry(
            name: 'test_rule',
            node: $node,
            tags: ['discount', 'vip'],
            priority: 10,
            version: '2.0',
            description: 'A test rule',
        );

        self::assertSame('test_rule', $entry->name);
        self::assertSame($node, $entry->node);
        self::assertSame(['discount', 'vip'], $entry->tags);
        self::assertSame(10, $entry->priority);
        self::assertSame('2.0', $entry->version);
        self::assertSame('A test rule', $entry->description);
    }

    public function testDefaultValues(): void
    {
        $node = new Value(1);

        $entry = new RuleEntry(name: 'minimal', node: $node);

        self::assertSame([], $entry->tags);
        self::assertSame(0, $entry->priority);
        self::assertSame('1.0', $entry->version);
        self::assertSame('', $entry->description);
    }
}
