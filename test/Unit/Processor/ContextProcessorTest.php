<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Processor;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TheChoice\Container;
use TheChoice\Context\ContextInterface;
use TheChoice\Exception\InvalidContextCalculation;
use TheChoice\Exception\RuntimeException;
use TheChoice\Node\Context;
use TheChoice\Node\Root;
use TheChoice\Node\Value;
use TheChoice\Operator\Equal;
use TheChoice\Processor\ContextProcessor;
use TheChoice\Trace\TraceCollector;

final class ContextProcessorTest extends TestCase
{
    // ─── Guard ────────────────────────────────────────────────────────────

    public function testProcessWithNonContextNodeThrowsException(): void
    {
        $container = new Container(['ctx' => ContextStub42::class]);
        /** @var ContextProcessor $processor */
        $processor = $container->get(ContextProcessor::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Context');

        $processor->process(new Value(1));
    }

    public function testProcessWithoutContextFactoryThrowsRuntimeException(): void
    {
        $container = new Container([]);

        // Create without factory (bypass createProcessor which sets it)
        $processor = new ContextProcessor();
        $processor->setContainer($container);

        $root = new Root();
        $contextNode = new Context();
        $contextNode->setContextName('anything');
        $contextNode->setRoot($root);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Context factory not configured');

        $processor->process($contextNode);
    }

    // ─── Basic evaluation ─────────────────────────────────────────────────

    public function testContextWithoutOperatorReturnsRawValue(): void
    {
        $container = new Container(['myCtx' => ContextStub42::class]);
        /** @var ContextProcessor $processor */
        $processor = $container->get(ContextProcessor::class);

        $root = new Root();
        $node = $this->makeContextNode('myCtx', $root);

        self::assertSame(42, $processor->process($node));
    }

    public function testContextWithOperatorReturnsBool(): void
    {
        $container = new Container(['myCtx' => ContextStub42::class]);
        /** @var ContextProcessor $processor */
        $processor = $container->get(ContextProcessor::class);

        $root = new Root();
        $node = $this->makeContextNode('myCtx', $root);
        $node->setOperator(new Equal()->setValue(42));

        self::assertTrue($processor->process($node));
    }

    public function testContextWithOperatorReturnsFalseWhenNotEqual(): void
    {
        $container = new Container(['myCtx' => ContextStub42::class]);
        /** @var ContextProcessor $processor */
        $processor = $container->get(ContextProcessor::class);

        $root = new Root();
        $node = $this->makeContextNode('myCtx', $root);
        $node->setOperator(new Equal()->setValue(99));

        self::assertFalse($processor->process($node));
    }

    // ─── Modifiers ────────────────────────────────────────────────────────

    public function testContextWithModifierTransformsValue(): void
    {
        // ContextStub4 returns 4; modifier '$context * 2' → 8
        $container = new Container(['myCtx' => ContextStub4::class]);
        /** @var ContextProcessor $processor */
        $processor = $container->get(ContextProcessor::class);

        $root = new Root();
        $node = $this->makeContextNode('myCtx', $root);
        $node->setModifiers(['$context * 2']);

        self::assertSame(8, $processor->process($node));
    }

    public function testContextWithModifierAndOperator(): void
    {
        // ContextStub4 returns 4; modifier '$context * 2' → 8; equal(8) → true
        $container = new Container(['myCtx' => ContextStub4::class]);
        /** @var ContextProcessor $processor */
        $processor = $container->get(ContextProcessor::class);

        $root = new Root();
        $node = $this->makeContextNode('myCtx', $root);
        $node->setModifiers(['$context * 2']);
        $node->setOperator(new Equal()->setValue(8));

        self::assertTrue($processor->process($node));
    }

    public function testContextWithStorageVariableInModifier(): void
    {
        // ContextStub4 returns 4; storage $factor=3; modifier '$context * $factor' → 12
        $container = new Container(['myCtx' => ContextStub4::class]);
        /** @var ContextProcessor $processor */
        $processor = $container->get(ContextProcessor::class);

        $root = new Root();
        $root->setGlobal('$factor', 3);

        $node = $this->makeContextNode('myCtx', $root);
        $node->setModifiers(['$context * $factor']);

        self::assertSame(12, $processor->process($node));
    }

    public function testInvalidModifierExpressionThrowsInvalidContextCalculation(): void
    {
        $container = new Container(['myCtx' => ContextStub4::class]);
        /** @var ContextProcessor $processor */
        $processor = $container->get(ContextProcessor::class);

        $root = new Root();
        $node = $this->makeContextNode('myCtx', $root);
        $node->setModifiers(['$context @@@ INVALID']);

        $this->expectException(InvalidContextCalculation::class);

        $processor->process($node);
    }

    public function testInvalidContextCalculationPreservesPreviousException(): void
    {
        $container = new Container(['myCtx' => ContextStub4::class]);
        /** @var ContextProcessor $processor */
        $processor = $container->get(ContextProcessor::class);

        $root = new Root();
        $node = $this->makeContextNode('myCtx', $root);
        $node->setModifiers(['$context @@@ INVALID']);

        try {
            $processor->process($node);
            self::fail('Expected InvalidContextCalculation was not thrown');
        } catch (InvalidContextCalculation $exception) {
            // After fix: previous exception must be preserved
            self::assertNotNull($exception->getPrevious());
        }
    }

    // ─── Caching ──────────────────────────────────────────────────────────

    public function testContextValueIsCachedBetweenCallsWithSameNode(): void
    {
        ContextStubCounting::reset();

        $container = new Container(['counting' => ContextStubCounting::class]);
        /** @var ContextProcessor $processor */
        $processor = $container->get(ContextProcessor::class);

        $root = new Root();
        $node = $this->makeContextNode('counting', $root);

        $result1 = $processor->process($node);
        $result2 = $processor->process($node);

        // getValue() should be called only once — second call uses cache
        self::assertSame(1, $result1);
        self::assertSame(1, $result2);
    }

    public function testFlushClearsCacheAndAllowsReevaluation(): void
    {
        ContextStubCounting::reset();

        $container = new Container(['counting' => ContextStubCounting::class]);
        /** @var ContextProcessor $processor */
        $processor = $container->get(ContextProcessor::class);

        $root = new Root();
        $node = $this->makeContextNode('counting', $root);

        $result1 = $processor->process($node);
        $processor->flush();
        $result2 = $processor->process($node);

        // After flush, getValue() is called again
        self::assertSame(1, $result1);
        self::assertSame(2, $result2);
    }

    // ─── Stoppable ────────────────────────────────────────────────────────

    public function testStoppableContextSetsRootResultAndReturnsNull(): void
    {
        $container = new Container(['myCtx' => ContextStub42::class]);
        /** @var ContextProcessor $processor */
        $processor = $container->get(ContextProcessor::class);

        $root = new Root();
        $node = $this->makeContextNode('myCtx', $root);
        $node->setStoppableType(Context::STOP_IMMEDIATELY);

        $result = $processor->process($node);

        self::assertNull($result);
        self::assertTrue($root->hasResult());
        self::assertSame(42, $root->getResult());
    }

    // ─── Trace ────────────────────────────────────────────────────────────

    public function testTraceIsRecordedForContextEvaluation(): void
    {
        $container = new Container(['myCtx' => ContextStub42::class]);
        /** @var ContextProcessor $processor */
        $processor = $container->get(ContextProcessor::class);

        $collector = new TraceCollector();
        $processor->setTraceCollector($collector);

        $root = new Root();
        $node = $this->makeContextNode('myCtx', $root);
        $node->setOperator(new Equal()->setValue(42));

        $collector->begin('Outer', 'outer');
        $processor->process($node);
        $collector->end(null);

        $rootEntry = $collector->getRoot();
        self::assertNotNull($rootEntry);

        $ctxEntry = $rootEntry->getChildren()[0];
        self::assertSame('Context', $ctxEntry->getNodeType());
        self::assertTrue($ctxEntry->getResult());
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    private function makeContextNode(string $contextName, Root $root): Context
    {
        $node = new Context();
        $node->setContextName($contextName);
        $node->setRoot($root);

        return $node;
    }
}

// ─── Stubs ────────────────────────────────────────────────────────────────

final class ContextStub42 implements ContextInterface
{
    public function getValue(): int
    {
        return 42;
    }
}

final class ContextStub4 implements ContextInterface
{
    public function getValue(): int
    {
        return 4;
    }
}

final class ContextStubCounting implements ContextInterface
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
