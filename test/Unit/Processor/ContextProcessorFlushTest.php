<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Processor;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use TheChoice\Builder\YamlBuilder;
use TheChoice\Container;
use TheChoice\Context\ContextInterface;
use TheChoice\Processor\ContextProcessor;
use TheChoice\Processor\RootProcessor;

final class ContextProcessorFlushTest extends TestCase
{
    // ─── flush clears processedContext cache via reflection ─────────────────

    public function testContextProcessorFlushResetsCache(): void
    {
        $processor = new ContextProcessor();

        $reflection = new ReflectionClass($processor);
        $property = $reflection->getProperty('processedContext');
        $property->setAccessible(true);
        $property->setValue($processor, ['someHash' => 'cachedValue']);

        self::assertNotEmpty($property->getValue($processor));

        $processor->flush();

        self::assertEmpty($property->getValue($processor));
    }

    // ─── flush called on every RootProcessor::process() invocation ──────────

    public function testRootProcessorFlushesBeforeEachEvaluation(): void
    {
        $container = new Container(['action' => StaticFiveContext::class]);
        $rootProcessor = $container->get(RootProcessor::class);
        $builder = $container->get(YamlBuilder::class);

        $yaml = "node: context\ncontext: action";

        $node1 = $builder->parse($yaml);
        $result1 = $rootProcessor->process($node1);

        // Reset builder counter so it can be reused
        $builder->resetNodesCount();

        $node2 = $builder->parse($yaml);
        $result2 = $rootProcessor->process($node2);

        self::assertSame(5, $result1);
        self::assertSame(5, $result2);
    }

    // ─── context invoked fresh after flush (stateful counter) ───────────────

    public function testContextIsReInvokedAfterFlush(): void
    {
        $container = new Container(['counter' => IncrementingContext::class]);
        $rootProcessor = $container->get(RootProcessor::class);
        $builder = $container->get(YamlBuilder::class);

        IncrementingContext::reset();

        $yaml = "node: context\ncontext: counter";

        $node1 = $builder->parse($yaml);
        $result1 = $rootProcessor->process($node1);

        $builder->resetNodesCount();
        $node2 = $builder->parse($yaml);
        $result2 = $rootProcessor->process($node2);

        // flush clears the ContextProcessor cache so getValue() is called again
        self::assertSame(1, $result1);
        self::assertSame(2, $result2);
    }
}

// ─── Stubs ────────────────────────────────────────────────────────────────

class StaticFiveContext implements ContextInterface
{
    public function getValue(): int
    {
        return 5;
    }
}

class IncrementingContext implements ContextInterface
{
    private static int $count = 0;

    public static function reset(): void
    {
        self::$count = 0;
    }

    public function getValue(): int
    {
        return ++self::$count;
    }
}
