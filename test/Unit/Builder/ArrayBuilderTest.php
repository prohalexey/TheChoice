<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Builder;

use PHPUnit\Framework\TestCase;
use TheChoice\Builder\ArrayBuilder;
use TheChoice\Container;
use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Exception\LogicException;
use TheChoice\Node\Collection;
use TheChoice\Node\Condition;
use TheChoice\Node\Context;
use TheChoice\Node\Root;
use TheChoice\Node\Value;

final class ArrayBuilderTest extends TestCase
{
    private ArrayBuilder $builder;

    protected function setUp(): void
    {
        $container = new Container([]);
        $this->builder = new ArrayBuilder($container);
    }

    // ─── Auto-wrap (short syntax) ───────────────────────────────────────────

    public function testAutoWrapsNonRootNodeIntoRoot(): void
    {
        $structure = ['node' => 'value', 'value' => 42];
        $result = $this->builder->build($structure);

        self::assertInstanceOf(Root::class, $result);
        self::assertInstanceOf(Value::class, $result->getRules());
    }

    public function testAutoWrapWorksForContextNode(): void
    {
        $structure = [
            'node'     => 'context',
            'context'  => 'something',
            'operator' => 'equal',
            'value'    => 1,
        ];

        // context without operator resolution still wraps in root
        $container = new Container([]);
        $builder = new ArrayBuilder($container);
        $result = $builder->build($structure);

        self::assertInstanceOf(Root::class, $result);
        self::assertInstanceOf(Context::class, $result->getRules());
    }

    // ─── nodesCount reset ───────────────────────────────────────────────────

    public function testBuildRequiresManualResetForReuse(): void
    {
        $structure1 = ['node' => 'value', 'value' => 1];
        $result1 = $this->builder->build($structure1);
        self::assertInstanceOf(Root::class, $result1);

        // build() does not auto-reset, so manual reset is needed
        $this->builder->resetNodesCount();

        $structure2 = ['node' => 'value', 'value' => 2];
        $result2 = $this->builder->build($structure2);
        self::assertInstanceOf(Root::class, $result2);
        self::assertSame(2, $result2->getRules()->getValue());
    }

    // ─── Root node constraints ──────────────────────────────────────────────

    public function testRootNodeAtTopLevelIsAccepted(): void
    {
        $structure = [
            'node'  => 'root',
            'rules' => ['node' => 'value', 'value' => 7],
        ];

        $result = $this->builder->build($structure);
        self::assertInstanceOf(Root::class, $result);
    }

    public function testRootNodeAsNonRootThrowsLogicException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('"Root" cannot be not root node');

        $structure = [
            'node' => 'condition',
            'if'   => ['node' => 'root', 'rules' => ['node' => 'value', 'value' => 0]],
            'then' => ['node' => 'value', 'value' => 1],
        ];

        $this->builder->build($structure);
    }

    // ─── Validation errors ──────────────────────────────────────────────────

    public function testMissingNodeKeyThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"node" property is absent');

        $structure = ['value' => 42];
        $this->builder->build($structure);
    }

    public function testNonStringNodeTypeThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Node type must be a string');

        $structure = ['node' => 123];
        $this->builder->build($structure);
    }

    public function testUnknownNodeTypeThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $structure = ['node' => 'nonexistent_node_type'];
        $this->builder->build($structure);
    }

    // ─── Correct node types ─────────────────────────────────────────────────

    public function testBuildsValueNodeCorrectly(): void
    {
        $structure = ['node' => 'value', 'value' => 'hello'];
        $root = $this->builder->build($structure);

        self::assertInstanceOf(Root::class, $root);
        $value = $root->getRules();
        self::assertInstanceOf(Value::class, $value);
        self::assertSame('hello', $value->getValue());
    }

    public function testBuildsCollectionNodeCorrectly(): void
    {
        $structure = [
            'node'  => 'collection',
            'type'  => 'and',
            'nodes' => [
                ['node' => 'value', 'value' => 1],
                ['node' => 'value', 'value' => 2],
            ],
        ];

        $root = $this->builder->build($structure);
        self::assertInstanceOf(Root::class, $root);

        $collection = $root->getRules();
        self::assertInstanceOf(Collection::class, $collection);
        self::assertCount(2, $collection->all());
    }

    public function testBuildsConditionNodeCorrectly(): void
    {
        $structure = [
            'node' => 'condition',
            'if'   => ['node' => 'value', 'value' => true],
            'then' => ['node' => 'value', 'value' => 1],
            'else' => ['node' => 'value', 'value' => 0],
        ];

        $root = $this->builder->build($structure);
        $condition = $root->getRules();

        self::assertInstanceOf(Condition::class, $condition);
        self::assertNotNull($condition->getElseNode());
    }
}
