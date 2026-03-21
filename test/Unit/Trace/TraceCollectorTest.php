<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Trace;

use PHPUnit\Framework\TestCase;
use TheChoice\Trace\TraceCollector;

final class TraceCollectorTest extends TestCase
{
    public function testEmptyCollectorReturnsNullRoot(): void
    {
        $collector = new TraceCollector();

        self::assertNull($collector->getRoot());
        self::assertFalse($collector->isActive());
    }

    public function testSingleBeginEndProducesRoot(): void
    {
        $collector = new TraceCollector();

        $collector->begin('Root', 'root');
        self::assertTrue($collector->isActive());

        $collector->end('result_value');
        self::assertFalse($collector->isActive());

        $root = $collector->getRoot();
        self::assertNotNull($root);
        self::assertSame('Root', $root->getNodeType());
        self::assertSame('root', $root->getNodeName());
        self::assertSame('result_value', $root->getResult());
        self::assertSame([], $root->getChildren());
    }

    public function testNestedEntriesFormTree(): void
    {
        $collector = new TraceCollector();

        $collector->begin('Root', 'root');
        $collector->begin('Collection', 'and');
        $collector->begin('Context', 'context1');
        $collector->end(true);
        $collector->begin('Context', 'context2');
        $collector->end(false);
        $collector->end(false);
        $collector->end(false);

        $root = $collector->getRoot();
        self::assertNotNull($root);
        self::assertCount(1, $root->getChildren());

        $collection = $root->getChildren()[0];
        self::assertSame('Collection', $collection->getNodeType());
        self::assertSame('and', $collection->getNodeName());
        self::assertCount(2, $collection->getChildren());

        self::assertSame('context1', $collection->getChildren()[0]->getNodeName());
        self::assertTrue($collection->getChildren()[0]->getResult());

        self::assertSame('context2', $collection->getChildren()[1]->getNodeName());
        self::assertFalse($collection->getChildren()[1]->getResult());
    }

    public function testEndOnEmptyStackDoesNothing(): void
    {
        $collector = new TraceCollector();
        $collector->end('value');

        self::assertNull($collector->getRoot());
    }
}
