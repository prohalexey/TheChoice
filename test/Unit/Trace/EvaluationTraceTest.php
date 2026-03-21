<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Trace;

use PHPUnit\Framework\TestCase;
use TheChoice\Trace\EvaluationTrace;
use TheChoice\Trace\TraceEntry;

final class EvaluationTraceTest extends TestCase
{
    public function testGetValueReturnsResult(): void
    {
        $entry = new TraceEntry('Root', 'root', 42);
        $trace = new EvaluationTrace(42, $entry);

        self::assertSame(42, $trace->getValue());
    }

    public function testGetTraceReturnsRootEntry(): void
    {
        $entry = new TraceEntry('Root', 'root', true);
        $trace = new EvaluationTrace(true, $entry);

        self::assertSame($entry, $trace->getTrace());
    }

    public function testExplainReturnsReadableOutput(): void
    {
        $root = new TraceEntry('Root', 'root', true);
        $collection = new TraceEntry('Collection', 'and', true);
        $ctx = new TraceEntry('Context', 'withdrawalCount equal', true);

        $collection->addChild($ctx);
        $root->addChild($collection);
        $root->setResult(true);

        $trace = new EvaluationTrace(true, $root);
        $explanation = $trace->explain();

        self::assertStringContainsString('Root[root]', $explanation);
        self::assertStringContainsString('Collection[and]', $explanation);
        self::assertStringContainsString('Context[withdrawalCount equal]', $explanation);
        self::assertStringContainsString('TRUE', $explanation);
    }
}
