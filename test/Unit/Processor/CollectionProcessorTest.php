<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Processor;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TheChoice\Container;
use TheChoice\Exception\LogicException;
use TheChoice\Node\Collection;
use TheChoice\Node\Root;
use TheChoice\Node\Value;
use TheChoice\Processor\CollectionProcessor;
use TheChoice\Trace\TraceCollector;

final class CollectionProcessorTest extends TestCase
{
    private CollectionProcessor $processor;

    private Root $root;

    protected function setUp(): void
    {
        $container = new Container([]);
        /** @var CollectionProcessor $processor */
        $processor = $container->get(CollectionProcessor::class);
        $this->processor = $processor;

        $this->root = new Root();
    }

    // ─── Guard ────────────────────────────────────────────────────────────

    public function testProcessWithNonCollectionNodeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Collection');

        $this->processor->process(new Value(1));
    }

    // ─── AND ──────────────────────────────────────────────────────────────

    public function testAndAllTrueReturnsTrue(): void
    {
        $collection = $this->buildCollection(Collection::TYPE_AND, [true, true, true]);

        self::assertTrue($this->processor->process($collection));
    }

    public function testAndOneFalseReturnsFalse(): void
    {
        $collection = $this->buildCollection(Collection::TYPE_AND, [true, false, true]);

        self::assertFalse($this->processor->process($collection));
    }

    public function testAndAllFalseReturnsFalse(): void
    {
        $collection = $this->buildCollection(Collection::TYPE_AND, [false, false]);

        self::assertFalse($this->processor->process($collection));
    }

    public function testAndEmptyCollectionReturnsTrue(): void
    {
        // Vacuously true: AND of nothing = true
        $collection = $this->buildCollection(Collection::TYPE_AND, []);

        self::assertTrue($this->processor->process($collection));
    }

    // ─── OR ───────────────────────────────────────────────────────────────

    public function testOrOneTrueReturnsTrue(): void
    {
        $collection = $this->buildCollection(Collection::TYPE_OR, [false, true, false]);

        self::assertTrue($this->processor->process($collection));
    }

    public function testOrAllFalseReturnsFalse(): void
    {
        $collection = $this->buildCollection(Collection::TYPE_OR, [false, false]);

        self::assertFalse($this->processor->process($collection));
    }

    // ─── NOT ──────────────────────────────────────────────────────────────

    public function testNotAllFalseReturnsTrue(): void
    {
        $collection = $this->buildCollection(Collection::TYPE_NOT, [false, false]);

        self::assertTrue($this->processor->process($collection));
    }

    public function testNotOneTrueReturnsFalse(): void
    {
        $collection = $this->buildCollection(Collection::TYPE_NOT, [false, true, false]);

        self::assertFalse($this->processor->process($collection));
    }

    public function testNotEmptyCollectionReturnsTrue(): void
    {
        $collection = $this->buildCollection(Collection::TYPE_NOT, []);

        self::assertTrue($this->processor->process($collection));
    }

    // ─── atLeast ──────────────────────────────────────────────────────────

    public function testAtLeastThrowsWhenCountNotSet(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('requires a "count" value');

        $collection = new Collection(Collection::TYPE_AT_LEAST);
        $collection->setRoot($this->root);

        $this->processor->process($collection);
    }

    public function testAtLeastTrueWhenEnoughPass(): void
    {
        $collection = $this->buildCollection(Collection::TYPE_AT_LEAST, [true, true, false]);
        $collection->setCount(2);

        self::assertTrue($this->processor->process($collection));
    }

    public function testAtLeastFalseWhenNotEnoughPass(): void
    {
        $collection = $this->buildCollection(Collection::TYPE_AT_LEAST, [true, false, false]);
        $collection->setCount(2);

        self::assertFalse($this->processor->process($collection));
    }

    public function testAtLeastWithCountZeroAlwaysReturnsTrue(): void
    {
        $collection = $this->buildCollection(Collection::TYPE_AT_LEAST, [false, false]);
        $collection->setCount(0);

        self::assertTrue($this->processor->process($collection));
    }

    public function testAtLeastExactBoundaryReturnsTrue(): void
    {
        $collection = $this->buildCollection(Collection::TYPE_AT_LEAST, [true, false, false]);
        $collection->setCount(1);

        self::assertTrue($this->processor->process($collection));
    }

    // ─── exactly ──────────────────────────────────────────────────────────

    public function testExactlyThrowsWhenCountNotSet(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('requires a "count" value');

        $collection = new Collection(Collection::TYPE_EXACTLY);
        $collection->setRoot($this->root);

        $this->processor->process($collection);
    }

    public function testExactlyTrueWhenCountMatches(): void
    {
        $collection = $this->buildCollection(Collection::TYPE_EXACTLY, [true, false, true]);
        $collection->setCount(2);

        self::assertTrue($this->processor->process($collection));
    }

    public function testExactlyFalseWhenCountDoesNotMatch(): void
    {
        $collection = $this->buildCollection(Collection::TYPE_EXACTLY, [true, true, true]);
        $collection->setCount(2);

        self::assertFalse($this->processor->process($collection));
    }

    public function testExactlyWithCountZeroAndAllFalseReturnsTrue(): void
    {
        $collection = $this->buildCollection(Collection::TYPE_EXACTLY, [false, false]);
        $collection->setCount(0);

        self::assertTrue($this->processor->process($collection));
    }

    public function testExactlyWithCountZeroAndOneTrueReturnsFalse(): void
    {
        $collection = $this->buildCollection(Collection::TYPE_EXACTLY, [true, false]);
        $collection->setCount(0);

        self::assertFalse($this->processor->process($collection));
    }

    // ─── Stoppable short-circuit via root.hasResult() ─────────────────────

    public function testAndShortCircuitsWhenRootHasResult(): void
    {
        // Simulate root having a result set (stoppable context scenario)
        $this->root->setResult('stopped');

        $collection = $this->buildCollection(Collection::TYPE_AND, [true]);

        // Root already has a result → AND should return null immediately
        $result = $this->processor->process($collection);

        self::assertNull($result);
    }

    // ─── Trace ────────────────────────────────────────────────────────────

    public function testTraceIsRecordedForAndCollection(): void
    {
        $collector = new TraceCollector();
        $this->processor->setTraceCollector($collector);

        $collection = $this->buildCollection(Collection::TYPE_AND, [true, false]);

        $collector->begin('Outer', 'outer');
        $this->processor->process($collection);
        $collector->end(null);

        $rootEntry = $collector->getRoot();
        self::assertNotNull($rootEntry);

        $collectionEntry = $rootEntry->getChildren()[0];
        self::assertSame('Collection', $collectionEntry->getNodeType());
        self::assertSame('and', $collectionEntry->getNodeName());
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    /**
     * @param array<bool|mixed> $values
     */
    private function buildCollection(string $type, array $values): Collection
    {
        $collection = new Collection($type);
        $collection->setRoot($this->root);

        foreach ($values as $value) {
            $node = new Value($value);
            $node->setRoot($this->root);
            $collection->add($node);
        }

        return $collection;
    }
}
