<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Processor;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TheChoice\Container;
use TheChoice\Node\Root;
use TheChoice\Node\Value;
use TheChoice\Processor\ValueProcessor;
use TheChoice\Trace\TraceCollector;

final class ValueProcessorTest extends TestCase
{
    private ValueProcessor $processor;

    protected function setUp(): void
    {
        $container = new Container([]);
        /** @var ValueProcessor $processor */
        $processor = $container->get(ValueProcessor::class);
        $this->processor = $processor;
    }

    // ─── Guard ────────────────────────────────────────────────────────────

    public function testProcessWithNonValueNodeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value');

        $root = new Root();
        $root->setRules(new Value(1));

        $this->processor->process($root);
    }

    // ─── Return values ────────────────────────────────────────────────────

    public function testProcessReturnsIntegerValue(): void
    {
        self::assertSame(42, $this->processor->process(new Value(42)));
    }

    public function testProcessReturnsStringValue(): void
    {
        self::assertSame('hello', $this->processor->process(new Value('hello')));
    }

    public function testProcessReturnsNullValue(): void
    {
        self::assertNull($this->processor->process(new Value(null)));
    }

    public function testProcessReturnsBoolValue(): void
    {
        self::assertTrue($this->processor->process(new Value(true)));
        self::assertFalse($this->processor->process(new Value(false)));
    }

    public function testProcessReturnsArrayValue(): void
    {
        $value = [1, 2, 3];

        self::assertSame($value, $this->processor->process(new Value($value)));
    }

    public function testProcessReturnsZero(): void
    {
        self::assertSame(0, $this->processor->process(new Value(0)));
    }

    public function testProcessReturnsEmptyString(): void
    {
        self::assertSame('', $this->processor->process(new Value('')));
    }

    // ─── Trace ────────────────────────────────────────────────────────────

    public function testTraceIsRecordedWhenCollectorIsSet(): void
    {
        $collector = new TraceCollector();
        $this->processor->setTraceCollector($collector);

        $collector->begin('test', 'wrapper');
        $this->processor->process(new Value(99));
        $collector->end(null);

        $root = $collector->getRoot();
        self::assertNotNull($root);

        $children = $root->getChildren();
        self::assertCount(1, $children);
        self::assertSame('Value', $children[0]->getNodeType());
        self::assertSame(99, $children[0]->getResult());
    }

    public function testNoTraceWhenCollectorIsNull(): void
    {
        // Should not throw even without a trace collector
        $result = $this->processor->process(new Value(1));
        self::assertSame(1, $result);
    }
}
