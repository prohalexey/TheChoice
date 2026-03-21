<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Exporter;

use PHPUnit\Framework\TestCase;
use TheChoice\Builder\RuleBuilder;
use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Exporter\NodeSerializer;
use TheChoice\Node\AbstractChildNode;
use TheChoice\Node\Collection;
use TheChoice\Node\Condition;
use TheChoice\Node\Context;
use TheChoice\Node\Root;
use TheChoice\Node\SwitchNode;
use TheChoice\Node\Value;
use TheChoice\Operator\IsEmpty;

final class NodeSerializerTest extends TestCase
{
    private NodeSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new NodeSerializer();
    }

    // ─── Value ────────────────────────────────────────────────────────────

    public function testSerializesIntValue(): void
    {
        $node = RuleBuilder::root()->rules(RuleBuilder::value(42))->build();

        $array = $this->serializer->toArray($node);

        self::assertSame(['node' => 'root', 'rules' => ['node' => 'value', 'value' => 42]], $array);
    }

    public function testSerializesBoolValue(): void
    {
        $node = new Value(true);

        self::assertSame(['node' => 'value', 'value' => true], $this->serializer->toArray($node));
    }

    public function testSerializesArrayValue(): void
    {
        $node = new Value(['a', 'b']);

        self::assertSame(['node' => 'value', 'value' => ['a', 'b']], $this->serializer->toArray($node));
    }

    public function testSerializesNullValue(): void
    {
        $node = new Value(null);

        self::assertSame(['node' => 'value', 'value' => null], $this->serializer->toArray($node));
    }

    public function testValueWithDescriptionIncludesIt(): void
    {
        $node = new Value(1);
        $node->setDescription('my value');

        $array = $this->serializer->toArray($node);

        self::assertSame('my value', $array['description']);
    }

    // ─── Context ──────────────────────────────────────────────────────────

    public function testSerializesContextWithoutOperator(): void
    {
        $node = RuleBuilder::context('depositCount')->build();

        $array = $this->serializer->toArray($node);

        self::assertSame(['node' => 'context', 'context' => 'depositCount'], $array);
    }

    public function testSerializesContextWithOperatorAndValue(): void
    {
        $node = RuleBuilder::context('depositCount')->greaterThan(5)->build();

        $array = $this->serializer->toArray($node);

        self::assertSame('greaterThan', $array['operator']);
        self::assertSame(5, $array['value']);
    }

    public function testSerializesIsEmptyOperatorWithoutValueKey(): void
    {
        $node = RuleBuilder::context('tags')->isEmpty()->build();

        $array = $this->serializer->toArray($node);

        self::assertSame('isEmpty', $array['operator']);
        self::assertArrayNotHasKey('value', $array);
    }

    public function testSerializesIsNullOperatorWithoutValueKey(): void
    {
        $node = RuleBuilder::context('field')->isNull()->build();

        $array = $this->serializer->toArray($node);

        self::assertSame('isNull', $array['operator']);
        self::assertArrayNotHasKey('value', $array);
    }

    public function testSerializesContextModifiers(): void
    {
        $node = RuleBuilder::context('amount')
            ->modifier('$context * 2')
            ->modifier('$context + 1')
            ->build()
        ;

        $array = $this->serializer->toArray($node);

        self::assertSame(['$context * 2', '$context + 1'], $array['modifiers']);
    }

    public function testSerializesContextParams(): void
    {
        $node = RuleBuilder::context('ctx')->params(['key' => 'val'])->build();

        self::assertSame(['key' => 'val'], $this->serializer->toArray($node)['params']);
    }

    public function testSerializesContextPriority(): void
    {
        $node = RuleBuilder::context('ctx')->priority(7)->build();

        self::assertSame(7, $this->serializer->toArray($node)['priority']);
    }

    public function testContextWithDefaultPriorityOmitsPriorityKey(): void
    {
        $node = RuleBuilder::context('ctx')->build();

        self::assertArrayNotHasKey('priority', $this->serializer->toArray($node));
    }

    public function testSerializesContextStoppable(): void
    {
        $node = RuleBuilder::context('ctx')->stoppable()->build();

        self::assertSame(Context::STOP_IMMEDIATELY, $this->serializer->toArray($node)['break']);
    }

    public function testNonStoppableContextOmitsBreakKey(): void
    {
        $node = RuleBuilder::context('ctx')->build();

        self::assertArrayNotHasKey('break', $this->serializer->toArray($node));
    }

    // ─── Condition ────────────────────────────────────────────────────────

    public function testSerializesConditionWithoutElse(): void
    {
        $node = RuleBuilder::root()
            ->rules(
                RuleBuilder::condition()
                    ->if(RuleBuilder::context('ctx')->equal(1))
                    ->then(RuleBuilder::value(true)),
            )
            ->build()
        ;

        $condArray = $this->serializer->toArray($node)['rules'];

        self::assertSame('condition', $condArray['node']);
        self::assertArrayHasKey('if', $condArray);
        self::assertArrayHasKey('then', $condArray);
        self::assertArrayNotHasKey('else', $condArray);
    }

    public function testSerializesConditionWithElse(): void
    {
        $node = RuleBuilder::root()
            ->rules(
                RuleBuilder::condition()
                    ->if(RuleBuilder::value(true))
                    ->then(RuleBuilder::value(1))
                    ->else(RuleBuilder::value(0)),
            )
            ->build()
        ;

        $condArray = $this->serializer->toArray($node)['rules'];

        self::assertArrayHasKey('else', $condArray);
        self::assertSame(0, $condArray['else']['value']);
    }

    public function testConditionPriorityIncludedWhenNonZero(): void
    {
        $cond = new Condition(new Value(true), new Value(1));
        $cond->setPriority(5);

        self::assertSame(5, $this->serializer->toArray($cond)['priority']);
    }

    // ─── Collection ───────────────────────────────────────────────────────

    public function testSerializesAndCollection(): void
    {
        $node = RuleBuilder::root()
            ->rules(
                RuleBuilder::collection('and')
                    ->add(RuleBuilder::context('a')->equal(1))
                    ->add(RuleBuilder::context('b')->equal(2)),
            )
            ->build()
        ;

        $collArray = $this->serializer->toArray($node)['rules'];

        self::assertSame('collection', $collArray['node']);
        self::assertSame('and', $collArray['type']);
        self::assertCount(2, $collArray['nodes']);
    }

    public function testSerializesAtLeastCollectionWithCount(): void
    {
        $collection = new Collection(Collection::TYPE_AT_LEAST);
        $collection->setCount(2);
        $collection->add(new Value(true));

        $array = $this->serializer->toArray($collection);

        self::assertSame(2, $array['count']);
    }

    public function testCollectionWithoutCountOmitsCountKey(): void
    {
        $collection = new Collection(Collection::TYPE_AND);
        $collection->add(new Value(true));

        self::assertArrayNotHasKey('count', $this->serializer->toArray($collection));
    }

    // ─── Root ─────────────────────────────────────────────────────────────

    public function testSerializesRootWithStorage(): void
    {
        $root = RuleBuilder::root()
            ->rules(RuleBuilder::value(1))
            ->storage(['$rate' => 0.1])
            ->build()
        ;

        $array = $this->serializer->toArray($root);

        self::assertSame(['$rate' => 0.1], $array['storage']);
    }

    public function testRootWithoutStorageOmitsStorageKey(): void
    {
        $root = RuleBuilder::root()->rules(RuleBuilder::value(1))->build();

        self::assertArrayNotHasKey('storage', $this->serializer->toArray($root));
    }

    public function testRootWithDescriptionIncludesIt(): void
    {
        $root = RuleBuilder::root()
            ->rules(RuleBuilder::value(1))
            ->description('My rules')
            ->build()
        ;

        self::assertSame('My rules', $this->serializer->toArray($root)['description']);
    }

    public function testRootWithoutDescriptionOmitsDescriptionKey(): void
    {
        $root = RuleBuilder::root()->rules(RuleBuilder::value(1))->build();

        self::assertArrayNotHasKey('description', $this->serializer->toArray($root));
    }

    // ─── SwitchNode ───────────────────────────────────────────────────────

    public function testSerializesSwitchNodeCases(): void
    {
        $node = RuleBuilder::root()
            ->rules(
                RuleBuilder::switch('userRole')
                    ->case('admin', RuleBuilder::value(100))
                    ->case('manager', RuleBuilder::value(50))
                    ->default(RuleBuilder::value(0)),
            )
            ->build()
        ;

        $switchArray = $this->serializer->toArray($node)['rules'];

        self::assertSame('switch', $switchArray['node']);
        self::assertSame('userRole', $switchArray['context']);
        self::assertCount(2, $switchArray['cases']);
        self::assertSame('equal', $switchArray['cases'][0]['operator']);
        self::assertSame('admin', $switchArray['cases'][0]['value']);
        self::assertSame(100, $switchArray['cases'][0]['then']['value']);
    }

    public function testSerializesSwitchNodeDefault(): void
    {
        $node = RuleBuilder::root()
            ->rules(
                RuleBuilder::switch('ctx')
                    ->case('x', RuleBuilder::value(1))
                    ->default(RuleBuilder::value(99)),
            )
            ->build()
        ;

        $switchArray = $this->serializer->toArray($node)['rules'];

        self::assertSame(99, $switchArray['default']['value']);
    }

    public function testSwitchNodeWithoutDefaultOmitsDefaultKey(): void
    {
        $node = RuleBuilder::root()
            ->rules(RuleBuilder::switch('ctx')->case('x', RuleBuilder::value(1)))
            ->build()
        ;

        self::assertArrayNotHasKey('default', $this->serializer->toArray($node)['rules']);
    }

    public function testSwitchCaseIsEmptyOperatorOmitsValueKey(): void
    {
        $node = RuleBuilder::root()
            ->rules(
                RuleBuilder::switch('ctx')
                    ->caseWith(new IsEmpty(), RuleBuilder::value(0)),
            )
            ->build()
        ;

        $caseArray = $this->serializer->toArray($node)['rules']['cases'][0];

        self::assertSame('isEmpty', $caseArray['operator']);
        self::assertArrayNotHasKey('value', $caseArray);
    }

    // ─── Unsupported node type ────────────────────────────────────────────

    public function testUnsupportedNodeTypeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $unknownNode = new class extends AbstractChildNode {
            public static function getNodeName(): string
            {
                return 'unknown';
            }
        };

        $this->serializer->toArray($unknownNode);
    }

    // ─── Deep nesting ─────────────────────────────────────────────────────

    public function testDeepNestedStructureIsFullySerialized(): void
    {
        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::condition()
                    ->if(
                        RuleBuilder::collection('and')
                            ->add(RuleBuilder::context('a')->equal(0))
                            ->add(RuleBuilder::context('b')->greaterThan(1)),
                    )
                    ->then(RuleBuilder::value(true))
                    ->else(RuleBuilder::value(false)),
            )
            ->build()
        ;

        $array = $this->serializer->toArray($root);

        $collNodes = $array['rules']['if']['nodes'];
        self::assertCount(2, $collNodes);
        self::assertSame('a', $collNodes[0]['context']);
        self::assertSame('b', $collNodes[1]['context']);
        self::assertTrue($array['rules']['then']['value']);
        self::assertFalse($array['rules']['else']['value']);
    }
}
