<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Builder;

use PHPUnit\Framework\TestCase;
use TheChoice\Builder\CollectionBuilder;
use TheChoice\Builder\ConditionBuilder;
use TheChoice\Builder\ContextBuilder;
use TheChoice\Builder\RootBuilder;
use TheChoice\Builder\RuleBuilder;
use TheChoice\Builder\SwitchBuilder;
use TheChoice\Builder\ValueBuilder;
use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Exception\LogicException;
use TheChoice\Node\Collection;
use TheChoice\Node\Condition;
use TheChoice\Node\Context;
use TheChoice\Node\Root;
use TheChoice\Node\SwitchNode;
use TheChoice\Node\Value;
use TheChoice\Operator\Equal;
use TheChoice\Operator\GreaterThan;
use TheChoice\Operator\IsEmpty;
use TheChoice\Operator\IsNull;

final class RuleBuilderTest extends TestCase
{
    // ─── RuleBuilder factory methods ─────────────────────────────────────

    public function testValueFactoryReturnsValueBuilder(): void
    {
        self::assertInstanceOf(ValueBuilder::class, RuleBuilder::value(42));
    }

    public function testContextFactoryReturnsContextBuilder(): void
    {
        self::assertInstanceOf(ContextBuilder::class, RuleBuilder::context('foo'));
    }

    public function testConditionFactoryReturnsConditionBuilder(): void
    {
        self::assertInstanceOf(ConditionBuilder::class, RuleBuilder::condition());
    }

    public function testCollectionFactoryReturnsCollectionBuilder(): void
    {
        self::assertInstanceOf(CollectionBuilder::class, RuleBuilder::collection('and'));
    }

    public function testSwitchFactoryReturnsSwitchBuilder(): void
    {
        self::assertInstanceOf(SwitchBuilder::class, RuleBuilder::switch('ctx'));
    }

    public function testRootFactoryReturnsRootBuilder(): void
    {
        self::assertInstanceOf(RootBuilder::class, RuleBuilder::root());
    }

    // ─── ValueBuilder ─────────────────────────────────────────────────────

    public function testValueBuilderBuildsValueNode(): void
    {
        $node = RuleBuilder::value(99)->build();

        self::assertInstanceOf(Value::class, $node);
        self::assertSame(99, $node->getValue());
    }

    public function testValueBuilderAcceptsAnyType(): void
    {
        self::assertSame('hello', RuleBuilder::value('hello')->build()->getValue());
        self::assertSame([1, 2], RuleBuilder::value([1, 2])->build()->getValue());
        self::assertNull(RuleBuilder::value(null)->build()->getValue());
    }

    // ─── ContextBuilder ────────────────────────────────────────────────────

    public function testContextBuilderBuildsContextNodeWithName(): void
    {
        $node = RuleBuilder::context('withdrawalCount')->build();

        self::assertInstanceOf(Context::class, $node);
        self::assertSame('withdrawalCount', $node->getContextName());
        self::assertNull($node->getOperator());
    }

    public function testContextBuilderWithEqualOperator(): void
    {
        $node = RuleBuilder::context('depositCount')->equal(2)->build();

        self::assertInstanceOf(Equal::class, $node->getOperator());
        self::assertSame(2, $node->getOperator()->getValue());
    }

    public function testContextBuilderWithGreaterThanOperator(): void
    {
        $node = RuleBuilder::context('visitCount')->greaterThan(5)->build();

        self::assertInstanceOf(GreaterThan::class, $node->getOperator());
        self::assertSame(5, $node->getOperator()->getValue());
    }

    public function testContextBuilderWithIsEmptyOperator(): void
    {
        $node = RuleBuilder::context('tags')->isEmpty()->build();

        self::assertInstanceOf(IsEmpty::class, $node->getOperator());
    }

    public function testContextBuilderWithIsNullOperator(): void
    {
        $node = RuleBuilder::context('field')->isNull()->build();

        self::assertInstanceOf(IsNull::class, $node->getOperator());
    }

    public function testContextBuilderSingleModifier(): void
    {
        $node = RuleBuilder::context('amount')->modifier('$context * 2')->build();

        self::assertSame(['$context * 2'], $node->getModifiers());
    }

    public function testContextBuilderMultipleModifiersViaChaining(): void
    {
        $node = RuleBuilder::context('amount')
            ->modifier('$context * 2')
            ->modifier('$context + 1')
            ->build()
        ;

        self::assertSame(['$context * 2', '$context + 1'], $node->getModifiers());
    }

    public function testContextBuilderModifiersReplacesAll(): void
    {
        $node = RuleBuilder::context('amount')
            ->modifier('old')
            ->modifiers(['new1', 'new2'])
            ->build()
        ;

        self::assertSame(['new1', 'new2'], $node->getModifiers());
    }

    public function testContextBuilderParams(): void
    {
        $node = RuleBuilder::context('data')->params(['key' => 'value'])->build();

        self::assertSame(['key' => 'value'], $node->getParams());
    }

    public function testContextBuilderPriority(): void
    {
        $node = RuleBuilder::context('foo')->priority(10)->build();

        self::assertSame(10, $node->getSortableValue());
    }

    public function testContextBuilderDescription(): void
    {
        $node = RuleBuilder::context('foo')->description('my ctx')->build();

        self::assertSame('my ctx', $node->getDescription());
    }

    public function testContextBuilderStoppable(): void
    {
        $node = RuleBuilder::context('foo')->stoppable()->build();

        self::assertTrue($node->isStoppable());
        self::assertSame(Context::STOP_IMMEDIATELY, $node->getStoppableType());
    }

    public function testContextBuilderOperatorIsReplacedOnSecondCall(): void
    {
        $node = RuleBuilder::context('foo')->equal(1)->greaterThan(5)->build();

        self::assertInstanceOf(GreaterThan::class, $node->getOperator());
    }

    // ─── ConditionBuilder ─────────────────────────────────────────────────

    public function testConditionBuilderBuildsConditionNode(): void
    {
        $node = RuleBuilder::condition()
            ->if(RuleBuilder::value(true))
            ->then(RuleBuilder::value(1))
            ->build()
        ;

        self::assertInstanceOf(Condition::class, $node);
        self::assertNull($node->getElseNode());
    }

    public function testConditionBuilderWithElse(): void
    {
        $node = RuleBuilder::condition()
            ->if(RuleBuilder::value(true))
            ->then(RuleBuilder::value(1))
            ->else(RuleBuilder::value(0))
            ->build()
        ;

        self::assertInstanceOf(Value::class, $node->getElseNode());
    }

    public function testConditionBuilderPriority(): void
    {
        $node = RuleBuilder::condition()
            ->if(RuleBuilder::value(true))
            ->then(RuleBuilder::value(1))
            ->priority(7)
            ->build()
        ;

        self::assertSame(7, $node->getSortableValue());
    }

    public function testConditionBuilderThrowsWhenIfIsMissing(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/if/');

        RuleBuilder::condition()->then(RuleBuilder::value(1))->build();
    }

    public function testConditionBuilderThrowsWhenThenIsMissing(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/then/');

        RuleBuilder::condition()->if(RuleBuilder::value(true))->build();
    }

    // ─── CollectionBuilder ────────────────────────────────────────────────

    public function testCollectionBuilderBuildsAndCollection(): void
    {
        $node = RuleBuilder::collection('and')
            ->add(RuleBuilder::value(true))
            ->add(RuleBuilder::value(false))
            ->build()
        ;

        self::assertInstanceOf(Collection::class, $node);
        self::assertSame('and', $node->getType());
        self::assertCount(2, $node->all());
    }

    public function testCollectionBuilderCount(): void
    {
        $node = RuleBuilder::collection('atLeast')
            ->count(2)
            ->add(RuleBuilder::value(true))
            ->build()
        ;

        self::assertSame(2, $node->getCount());
    }

    public function testCollectionBuilderInvalidTypeThrows(): void
    {
        $this->expectException(LogicException::class);

        RuleBuilder::collection('xor')->build();
    }

    public function testCollectionBuilderPriority(): void
    {
        $node = RuleBuilder::collection('or')->priority(3)->build();

        self::assertSame(3, $node->getSortableValue());
    }

    // ─── SwitchBuilder ────────────────────────────────────────────────────

    public function testSwitchBuilderBuildsSwitchNode(): void
    {
        $node = RuleBuilder::switch('userRole')
            ->case('admin', RuleBuilder::value(100))
            ->build()
        ;

        self::assertInstanceOf(SwitchNode::class, $node);
        self::assertSame('userRole', $node->getContextName());
        self::assertCount(1, $node->getCases());
    }

    public function testSwitchBuilderCaseUsesEqualOperatorByDefault(): void
    {
        $node = RuleBuilder::switch('role')
            ->case('admin', RuleBuilder::value(1))
            ->build()
        ;

        self::assertInstanceOf(Equal::class, $node->getCases()[0]->getOperator());
        self::assertSame('admin', $node->getCases()[0]->getOperator()->getValue());
    }

    public function testSwitchBuilderCaseOp(): void
    {
        $node = RuleBuilder::switch('amount')
            ->caseOp('greaterThan', 1000, RuleBuilder::value('gold'))
            ->build()
        ;

        self::assertInstanceOf(GreaterThan::class, $node->getCases()[0]->getOperator());
        self::assertSame(1000, $node->getCases()[0]->getOperator()->getValue());
    }

    public function testSwitchBuilderCaseWith(): void
    {
        $op = new GreaterThan()->setValue(5000);

        $node = RuleBuilder::switch('sum')
            ->caseWith($op, RuleBuilder::value('platinum'))
            ->build()
        ;

        self::assertSame($op, $node->getCases()[0]->getOperator());
    }

    public function testSwitchBuilderDefault(): void
    {
        $node = RuleBuilder::switch('role')
            ->default(RuleBuilder::value(0))
            ->build()
        ;

        self::assertInstanceOf(Value::class, $node->getDefaultNode());
    }

    public function testSwitchBuilderNoDefault(): void
    {
        $node = RuleBuilder::switch('role')->build();

        self::assertNull($node->getDefaultNode());
    }

    public function testSwitchBuilderCaseOpUnknownOperatorThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RuleBuilder::switch('foo')->caseOp('nonExistent', 1, RuleBuilder::value(0))->build();
    }

    // ─── RootBuilder ──────────────────────────────────────────────────────

    public function testRootBuilderBuildsRootNode(): void
    {
        $root = RuleBuilder::root()
            ->rules(RuleBuilder::value(42))
            ->build()
        ;

        self::assertInstanceOf(Root::class, $root);
        self::assertInstanceOf(Value::class, $root->getRules());
    }

    public function testRootBuilderStorage(): void
    {
        $root = RuleBuilder::root()
            ->rules(RuleBuilder::value(1))
            ->storage(['$rate' => 10])
            ->build()
        ;

        self::assertSame(10, $root->getStorageValue('$rate'));
    }

    public function testRootBuilderDescription(): void
    {
        $root = RuleBuilder::root()
            ->rules(RuleBuilder::value(1))
            ->description('My rule')
            ->build()
        ;

        self::assertSame('My rule', $root->getDescription());
    }

    public function testRootBuilderThrowsWhenRulesNotSet(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/rules/');

        RuleBuilder::root()->build();
    }

    public function testRootBuilderPropagatesRootToValueNode(): void
    {
        $root = RuleBuilder::root()
            ->rules(RuleBuilder::value(1))
            ->build()
        ;

        /** @var Value $valueNode */
        $valueNode = $root->getRules();
        self::assertSame($root, $valueNode->getRoot());
    }

    public function testRootBuilderPropagatesRootThroughCondition(): void
    {
        $ifCtx = RuleBuilder::context('foo')->equal(1);
        $thenCtx = RuleBuilder::context('bar');

        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::condition()->if($ifCtx)->then($thenCtx),
            )
            ->build()
        ;

        /** @var Condition $condition */
        $condition = $root->getRules();
        self::assertSame($root, $condition->getRoot());
        self::assertSame($root, $condition->getIfNode()->getRoot());
        self::assertSame($root, $condition->getThenNode()->getRoot());
    }

    public function testRootBuilderPropagatesRootThroughCollection(): void
    {
        $ctx1 = RuleBuilder::context('a');
        $ctx2 = RuleBuilder::context('b');

        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::collection('and')->add($ctx1)->add($ctx2),
            )
            ->build()
        ;

        /** @var Collection $collection */
        $collection = $root->getRules();
        self::assertSame($root, $collection->getRoot());
        self::assertSame($root, $collection->all()[0]->getRoot());
        self::assertSame($root, $collection->all()[1]->getRoot());
    }

    public function testRootBuilderPropagatesRootThroughSwitchNode(): void
    {
        $thenBuilder = RuleBuilder::value(1);

        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::switch('role')
                    ->case('admin', $thenBuilder)
                    ->default(RuleBuilder::value(0)),
            )
            ->build()
        ;

        /** @var SwitchNode $switchNode */
        $switchNode = $root->getRules();
        self::assertSame($root, $switchNode->getRoot());
        self::assertSame($root, $switchNode->getCases()[0]->getThenNode()->getRoot());
        self::assertSame($root, $switchNode->getDefaultNode()->getRoot());
    }
}
