<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Trace;

use PHPUnit\Framework\TestCase;
use TheChoice\Trace\TraceEntry;

final class TraceEntryTest extends TestCase
{
    public function testGettersReturnConstructorValues(): void
    {
        $entry = new TraceEntry('Context', 'depositCount', true);

        self::assertSame('Context', $entry->getNodeType());
        self::assertSame('depositCount', $entry->getNodeName());
        self::assertTrue($entry->getResult());
        self::assertSame([], $entry->getChildren());
    }

    public function testSetResult(): void
    {
        $entry = new TraceEntry('Value', 'value');
        self::assertNull($entry->getResult());

        $entry->setResult(42);
        self::assertSame(42, $entry->getResult());
    }

    public function testAddChild(): void
    {
        $parent = new TraceEntry('Collection', 'and');
        $child1 = new TraceEntry('Context', 'ctx1', true);
        $child2 = new TraceEntry('Context', 'ctx2', false);

        $parent->addChild($child1);
        $parent->addChild($child2);

        self::assertCount(2, $parent->getChildren());
        self::assertSame($child1, $parent->getChildren()[0]);
        self::assertSame($child2, $parent->getChildren()[1]);
    }

    public function testToStringFormatsTrueResult(): void
    {
        $entry = new TraceEntry('Context', 'test', true);
        self::assertStringContainsString('TRUE', $entry->toString());
    }

    public function testToStringFormatsFalseResult(): void
    {
        $entry = new TraceEntry('Context', 'test', false);
        self::assertStringContainsString('FALSE', $entry->toString());
    }

    public function testToStringFormatsNullResult(): void
    {
        $entry = new TraceEntry('Context', 'test', null);
        self::assertStringContainsString('null', $entry->toString());
    }

    public function testToStringFormatsNumericResult(): void
    {
        $entry = new TraceEntry('Value', 'value', 42);
        self::assertStringContainsString('42', $entry->toString());
    }

    public function testToStringFormatsStringResult(): void
    {
        $entry = new TraceEntry('Value', 'value', 'hello');
        self::assertStringContainsString('"hello"', $entry->toString());
    }

    public function testToStringFormatsArrayResult(): void
    {
        $entry = new TraceEntry('Value', 'value', [1, 2, 3]);
        self::assertStringContainsString('[1,2,3]', $entry->toString());
    }

    public function testToStringWithChildren(): void
    {
        $parent = new TraceEntry('Collection', 'and', true);
        $child = new TraceEntry('Context', 'ctx', true);
        $parent->addChild($child);

        $output = $parent->toString();
        $lines = explode("\n", $output);

        self::assertCount(2, $lines);
        self::assertStringContainsString('Collection[and]', $lines[0]);
        self::assertStringContainsString('Context[ctx]', $lines[1]);
        // Child line should be indented
        self::assertStringStartsWith('  ', $lines[1]);
    }

    public function testToStringWithIndent(): void
    {
        $entry = new TraceEntry('Value', 'value', 5);

        $output = $entry->toString(2);
        self::assertStringStartsWith('    ', $output);
    }
}
