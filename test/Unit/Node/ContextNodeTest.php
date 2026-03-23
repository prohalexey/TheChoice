<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Node;

use PHPUnit\Framework\TestCase;
use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Exception\LogicException;
use TheChoice\Node\Context;
use TheChoice\Node\Root;

/**
 * Unit tests for the Context node.
 */
final class ContextNodeTest extends TestCase
{
    private Context $node;

    protected function setUp(): void
    {
        $this->node = new Context();
    }

    // ─── contextName — nullable default (after fix) ───────────────────────

    public function testContextNameIsNullByDefault(): void
    {
        self::assertNull($this->node->getContextName());
    }

    public function testSetAndGetContextName(): void
    {
        $this->node->setContextName('myContext');

        self::assertSame('myContext', $this->node->getContextName());
    }

    public function testSetContextNameReturnsSelf(): void
    {
        $result = $this->node->setContextName('ctx');

        self::assertSame($this->node, $result);
    }

    // ─── priority / Sortable ──────────────────────────────────────────────

    public function testDefaultPriorityIsZero(): void
    {
        self::assertSame(0, $this->node->getSortableValue());
    }

    public function testSetAndGetPriority(): void
    {
        $this->node->setPriority(10);

        self::assertSame(10, $this->node->getSortableValue());
    }

    // ─── stoppable ────────────────────────────────────────────────────────

    public function testIsNotStoppableByDefault(): void
    {
        self::assertFalse($this->node->isStoppable());
        self::assertNull($this->node->getStoppableType());
    }

    public function testSetStoppableTypeImmediately(): void
    {
        $this->node->setStoppableType(Context::STOP_IMMEDIATELY);

        self::assertTrue($this->node->isStoppable());
        self::assertSame(Context::STOP_IMMEDIATELY, $this->node->getStoppableType());
    }

    public function testSetStoppableTypeWithNullThrowsLogicException(): void
    {
        $this->expectException(LogicException::class);

        // @phpstan-ignore argument.type
        $this->node->setStoppableType(null);
    }

    public function testSetStoppableTypeWithUnknownValueThrowsLogicException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Stoppable type must be one of');

        $this->node->setStoppableType('unknown_stop_mode');
    }

    public function testGetStopModesContainsImmediately(): void
    {
        self::assertContains(Context::STOP_IMMEDIATELY, Context::getStopModes());
    }

    // ─── modifiers ────────────────────────────────────────────────────────

    public function testModifiersDefaultToEmptyArray(): void
    {
        self::assertSame([], $this->node->getModifiers());
    }

    public function testSetModifiersWithValidStrings(): void
    {
        $this->node->setModifiers(['$context * 2', '$context + 1']);

        self::assertSame(['$context * 2', '$context + 1'], $this->node->getModifiers());
    }

    public function testSetModifiersWithEmptyArraySucceeds(): void
    {
        $this->node->setModifiers([]);

        self::assertSame([], $this->node->getModifiers());
    }

    public function testSetModifiersWithNonStringElementThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('modifier must be string type');

        // @phpstan-ignore argument.type
        $this->node->setModifiers(['valid', 42]);
    }

    // ─── params ───────────────────────────────────────────────────────────

    public function testParamsDefaultToEmptyArray(): void
    {
        self::assertSame([], $this->node->getParams());
    }

    public function testSetAndGetParams(): void
    {
        $this->node->setParams(['key' => 'value', 'num' => 42]);

        self::assertSame(['key' => 'value', 'num' => 42], $this->node->getParams());
    }

    // ─── operator ─────────────────────────────────────────────────────────

    public function testOperatorIsNullByDefault(): void
    {
        self::assertNull($this->node->getOperator());
    }

    // ─── root propagation ─────────────────────────────────────────────────

    public function testSetAndGetRoot(): void
    {
        $root = new Root();
        $this->node->setRoot($root);

        self::assertSame($root, $this->node->getRoot());
    }

    // ─── node name ────────────────────────────────────────────────────────

    public function testGetNodeNameReturnsContext(): void
    {
        self::assertSame('context', Context::getNodeName());
    }
}
