<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Processor;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TheChoice\Container;
use TheChoice\Node\Condition;
use TheChoice\Node\Root;
use TheChoice\Node\Value;
use TheChoice\Processor\ConditionProcessor;
use TheChoice\Trace\TraceCollector;

final class ConditionProcessorTest extends TestCase
{
    private ConditionProcessor $processor;

    private Root $root;

    protected function setUp(): void
    {
        $container = new Container([]);
        /** @var ConditionProcessor $processor */
        $processor = $container->get(ConditionProcessor::class);
        $this->processor = $processor;

        $this->root = new Root();
    }

    // ─── Guard ────────────────────────────────────────────────────────────

    public function testProcessWithNonConditionNodeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Condition');

        $this->processor->process(new Value(1));
    }

    // ─── if = true → then ─────────────────────────────────────────────────

    public function testIfTrueExecutesThenBranch(): void
    {
        $condition = $this->buildCondition(true, 'then-result', 'else-result');

        self::assertSame('then-result', $this->processor->process($condition));
    }

    // ─── if = false → else ────────────────────────────────────────────────

    public function testIfFalseExecutesElseBranch(): void
    {
        $condition = $this->buildCondition(false, 'then-result', 'else-result');

        self::assertSame('else-result', $this->processor->process($condition));
    }

    // ─── if = null → else ─────────────────────────────────────────────────

    public function testIfNullExecutesElseBranch(): void
    {
        $condition = $this->buildCondition(null, 'then-result', 'else-result');

        self::assertSame('else-result', $this->processor->process($condition));
    }

    // ─── Strict check: non-bool truthy values do NOT trigger then ─────────

    /**
     * After fix: if branch uses === true, not truthy check.
     * Value(42) is truthy but not === true → goes to else.
     */
    public function testIfNonBoolIntegerDoesNotTriggerThenBranch(): void
    {
        $condition = $this->buildCondition(42, 'then-result', 'else-result');

        self::assertSame('else-result', $this->processor->process($condition));
    }

    public function testIfZeroDoesNotTriggerThenBranch(): void
    {
        $condition = $this->buildCondition(0, 'then-result', 'else-result');

        self::assertSame('else-result', $this->processor->process($condition));
    }

    public function testIfNonEmptyStringDoesNotTriggerThenBranch(): void
    {
        $condition = $this->buildCondition('non-empty', 'then-result', 'else-result');

        self::assertSame('else-result', $this->processor->process($condition));
    }

    public function testIfEmptyStringDoesNotTriggerThenBranch(): void
    {
        $condition = $this->buildCondition('', 'then-result', 'else-result');

        self::assertSame('else-result', $this->processor->process($condition));
    }

    // ─── No else branch ───────────────────────────────────────────────────

    public function testIfFalseWithNoElseBranchReturnsFalse(): void
    {
        $condition = $this->buildConditionNoElse(false, 'then-result');

        self::assertFalse($this->processor->process($condition));
    }

    public function testIfNullWithNoElseBranchReturnsFalse(): void
    {
        $condition = $this->buildConditionNoElse(null, 'then-result');

        self::assertFalse($this->processor->process($condition));
    }

    public function testIfTrueWithNoElseBranchReturnsThenValue(): void
    {
        $condition = $this->buildConditionNoElse(true, 'then-result');

        self::assertSame('then-result', $this->processor->process($condition));
    }

    // ─── Then/else can return any value ───────────────────────────────────

    public function testThenBranchCanReturnNull(): void
    {
        $condition = $this->buildCondition(true, null, 'else-result');

        self::assertNull($this->processor->process($condition));
    }

    public function testElseBranchCanReturnFalse(): void
    {
        $condition = $this->buildCondition(false, 'then-result', false);

        self::assertFalse($this->processor->process($condition));
    }

    // ─── Trace ────────────────────────────────────────────────────────────

    public function testTraceIsRecordedForConditionWithThenBranch(): void
    {
        $collector = new TraceCollector();
        $this->processor->setTraceCollector($collector);

        $condition = $this->buildCondition(true, 'then-result', 'else-result');

        $collector->begin('Outer', 'outer');
        $this->processor->process($condition);
        $collector->end(null);

        $rootEntry = $collector->getRoot();
        self::assertNotNull($rootEntry);

        $conditionEntry = $rootEntry->getChildren()[0];
        self::assertSame('Condition', $conditionEntry->getNodeType());
        self::assertSame('then-result', $conditionEntry->getResult());
    }

    public function testTraceIsRecordedForConditionWithElseBranch(): void
    {
        $collector = new TraceCollector();
        $this->processor->setTraceCollector($collector);

        $condition = $this->buildCondition(false, 'then-result', 'else-result');

        $collector->begin('Outer', 'outer');
        $this->processor->process($condition);
        $collector->end(null);

        $rootEntry = $collector->getRoot();
        self::assertNotNull($rootEntry);

        $conditionEntry = $rootEntry->getChildren()[0];
        self::assertSame('Condition', $conditionEntry->getNodeType());
        self::assertSame('else-result', $conditionEntry->getResult());
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    private function buildCondition(mixed $ifValue, mixed $thenValue, mixed $elseValue): Condition
    {
        $ifNode = new Value($ifValue);
        $thenNode = new Value($thenValue);
        $elseNode = new Value($elseValue);

        $ifNode->setRoot($this->root);
        $thenNode->setRoot($this->root);
        $elseNode->setRoot($this->root);

        $condition = new Condition($ifNode, $thenNode, $elseNode);
        $condition->setRoot($this->root);

        return $condition;
    }

    private function buildConditionNoElse(mixed $ifValue, mixed $thenValue): Condition
    {
        $ifNode = new Value($ifValue);
        $thenNode = new Value($thenValue);

        $ifNode->setRoot($this->root);
        $thenNode->setRoot($this->root);

        $condition = new Condition($ifNode, $thenNode);
        $condition->setRoot($this->root);

        return $condition;
    }
}
