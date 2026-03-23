<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Processor;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TheChoice\Container;
use TheChoice\Context\ContextInterface;
use TheChoice\Exception\RuntimeException;
use TheChoice\Node\Root;
use TheChoice\Node\SwitchCase;
use TheChoice\Node\SwitchNode;
use TheChoice\Node\Value;
use TheChoice\Operator\Equal;
use TheChoice\Operator\GreaterThan;
use TheChoice\Processor\SwitchProcessor;
use TheChoice\Trace\TraceCollector;

final class SwitchProcessorTest extends TestCase
{
    private Container $container;

    private Root $root;

    protected function setUp(): void
    {
        // 'role' context returns 'admin'
        $this->container = new Container([
            'role' => SwitchTestRoleContext::class,
        ]);
        $this->root = new Root();
    }

    // ─── Guard ────────────────────────────────────────────────────────────

    public function testProcessWithNonSwitchNodeThrowsException(): void
    {
        /** @var SwitchProcessor $processor */
        $processor = $this->container->get(SwitchProcessor::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SwitchNode');

        $processor->process(new Value(1));
    }

    public function testProcessWithoutContextFactoryThrowsRuntimeException(): void
    {
        // Create manually without factory
        $processor = new SwitchProcessor();
        $processor->setContainer($this->container);

        $switchNode = new SwitchNode('role', []);
        $switchNode->setRoot($this->root);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Context factory not configured');

        $processor->process($switchNode);
    }

    // ─── Matching cases ───────────────────────────────────────────────────

    public function testMatchingCaseReturnsThenValue(): void
    {
        /** @var SwitchProcessor $processor */
        $processor = $this->container->get(SwitchProcessor::class);

        $thenNode = new Value('admin-panel');
        $thenNode->setRoot($this->root);

        $cases = [
            new SwitchCase(new Equal()->setValue('admin'), $thenNode),
        ];

        $switchNode = new SwitchNode('role', $cases);
        $switchNode->setRoot($this->root);

        self::assertSame('admin-panel', $processor->process($switchNode));
    }

    public function testFirstMatchingCaseIsUsedWhenMultipleMatch(): void
    {
        /** @var SwitchProcessor $processor */
        $processor = $this->container->get(SwitchProcessor::class);

        $firstThen = new Value('first-match');
        $firstThen->setRoot($this->root);

        $secondThen = new Value('second-match');
        $secondThen->setRoot($this->root);

        $cases = [
            new SwitchCase(new Equal()->setValue('admin'), $firstThen),
            new SwitchCase(new Equal()->setValue('admin'), $secondThen),
        ];

        $switchNode = new SwitchNode('role', $cases);
        $switchNode->setRoot($this->root);

        self::assertSame('first-match', $processor->process($switchNode));
    }

    public function testNonMatchingFirstCaseSkippedToMatchingSecond(): void
    {
        /** @var SwitchProcessor $processor */
        $processor = $this->container->get(SwitchProcessor::class);

        $managerThen = new Value('manager-panel');
        $managerThen->setRoot($this->root);

        $adminThen = new Value('admin-panel');
        $adminThen->setRoot($this->root);

        $cases = [
            new SwitchCase(new Equal()->setValue('manager'), $managerThen),
            new SwitchCase(new Equal()->setValue('admin'), $adminThen),
        ];

        $switchNode = new SwitchNode('role', $cases);
        $switchNode->setRoot($this->root);

        self::assertSame('admin-panel', $processor->process($switchNode));
    }

    // ─── Default fallback ─────────────────────────────────────────────────

    public function testNoMatchReturnsDefaultValue(): void
    {
        /** @var SwitchProcessor $processor */
        $processor = $this->container->get(SwitchProcessor::class);

        $nonMatchThen = new Value('manager-panel');
        $nonMatchThen->setRoot($this->root);

        $defaultNode = new Value('default-panel');
        $defaultNode->setRoot($this->root);

        $cases = [
            new SwitchCase(new Equal()->setValue('manager'), $nonMatchThen),
        ];

        $switchNode = new SwitchNode('role', $cases, $defaultNode);
        $switchNode->setRoot($this->root);

        self::assertSame('default-panel', $processor->process($switchNode));
    }

    public function testNoMatchAndNoDefaultReturnsNull(): void
    {
        /** @var SwitchProcessor $processor */
        $processor = $this->container->get(SwitchProcessor::class);

        $nonMatchThen = new Value('manager-panel');
        $nonMatchThen->setRoot($this->root);

        $cases = [
            new SwitchCase(new Equal()->setValue('manager'), $nonMatchThen),
        ];

        $switchNode = new SwitchNode('role', $cases);
        $switchNode->setRoot($this->root);

        self::assertNull($processor->process($switchNode));
    }

    public function testEmptyCasesWithDefaultReturnsDefault(): void
    {
        /** @var SwitchProcessor $processor */
        $processor = $this->container->get(SwitchProcessor::class);

        $defaultNode = new Value('default-result');
        $defaultNode->setRoot($this->root);

        $switchNode = new SwitchNode('role', [], $defaultNode);
        $switchNode->setRoot($this->root);

        self::assertSame('default-result', $processor->process($switchNode));
    }

    public function testEmptyCasesWithNoDefaultReturnsNull(): void
    {
        /** @var SwitchProcessor $processor */
        $processor = $this->container->get(SwitchProcessor::class);

        $switchNode = new SwitchNode('role', []);
        $switchNode->setRoot($this->root);

        self::assertNull($processor->process($switchNode));
    }

    // ─── Non-Equal operators in cases ─────────────────────────────────────

    public function testCaseWithGreaterThanOperator(): void
    {
        // 'score' context returns 150
        $container = new Container(['score' => SwitchTestScoreContext::class]);
        /** @var SwitchProcessor $processor */
        $processor = $container->get(SwitchProcessor::class);

        $highThen = new Value('high');
        $highThen->setRoot($this->root);

        $lowThen = new Value('low');
        $lowThen->setRoot($this->root);

        $cases = [
            new SwitchCase(new GreaterThan()->setValue(200), $highThen),
            new SwitchCase(new GreaterThan()->setValue(100), $lowThen),
        ];

        $switchNode = new SwitchNode('score', $cases);
        $switchNode->setRoot($this->root);

        // score=150, not > 200, but > 100 → 'low'
        self::assertSame('low', $processor->process($switchNode));
    }

    // ─── Trace ────────────────────────────────────────────────────────────

    public function testTraceIsRecordedOnCaseMatch(): void
    {
        /** @var SwitchProcessor $processor */
        $processor = $this->container->get(SwitchProcessor::class);

        $collector = new TraceCollector();
        $processor->setTraceCollector($collector);

        $thenNode = new Value('admin-panel');
        $thenNode->setRoot($this->root);

        $cases = [new SwitchCase(new Equal()->setValue('admin'), $thenNode)];
        $switchNode = new SwitchNode('role', $cases);
        $switchNode->setRoot($this->root);

        $collector->begin('Outer', 'outer');
        $processor->process($switchNode);
        $collector->end(null);

        $rootEntry = $collector->getRoot();
        self::assertNotNull($rootEntry);

        $switchEntry = $rootEntry->getChildren()[0];
        self::assertSame('Switch', $switchEntry->getNodeType());
        self::assertSame('role', $switchEntry->getNodeName());
        self::assertSame('admin-panel', $switchEntry->getResult());
    }

    public function testTraceIsRecordedOnDefault(): void
    {
        /** @var SwitchProcessor $processor */
        $processor = $this->container->get(SwitchProcessor::class);

        $collector = new TraceCollector();
        $processor->setTraceCollector($collector);

        $defaultNode = new Value('default-panel');
        $defaultNode->setRoot($this->root);

        $switchNode = new SwitchNode('role', [], $defaultNode);
        $switchNode->setRoot($this->root);

        $collector->begin('Outer', 'outer');
        $processor->process($switchNode);
        $collector->end(null);

        $rootEntry = $collector->getRoot();
        self::assertNotNull($rootEntry);
        self::assertSame('default-panel', $rootEntry->getChildren()[0]->getResult());
    }
}

// ─── Stubs ────────────────────────────────────────────────────────────────

final class SwitchTestRoleContext implements ContextInterface
{
    public function getValue(): string
    {
        return 'admin';
    }
}

final class SwitchTestScoreContext implements ContextInterface
{
    public function getValue(): int
    {
        return 150;
    }
}
