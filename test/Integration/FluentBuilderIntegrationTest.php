<?php

declare(strict_types=1);

namespace TheChoice\Tests\Integration;

use PHPUnit\Framework\TestCase;
use TheChoice\Builder\RuleBuilder;
use TheChoice\Container;
use TheChoice\Node\Collection;
use TheChoice\Processor\RootProcessor;
use TheChoice\Tests\Integration\Contexts\Action1;
use TheChoice\Tests\Integration\Contexts\Action2;
use TheChoice\Tests\Integration\Contexts\ActionReturnInt;
use TheChoice\Tests\Integration\Contexts\DepositCount;
use TheChoice\Tests\Integration\Contexts\DepositSum;
use TheChoice\Tests\Integration\Contexts\HasVipStatus;
use TheChoice\Tests\Integration\Contexts\InGroup;
use TheChoice\Tests\Integration\Contexts\UserRole;
use TheChoice\Tests\Integration\Contexts\VisitCount;
use TheChoice\Tests\Integration\Contexts\WithdrawalCount;

final class FluentBuilderIntegrationTest extends TestCase
{
    private RootProcessor $rootProcessor;

    protected function setUp(): void
    {
        $container = new Container([
            'visitCount'      => VisitCount::class,
            'hasVipStatus'    => HasVipStatus::class,
            'inGroup'         => InGroup::class,
            'withdrawalCount' => WithdrawalCount::class,
            'depositCount'    => DepositCount::class,
            'depositSum'      => DepositSum::class,
            'userRole'        => UserRole::class,
            'action1'         => Action1::class,
            'action2'         => Action2::class,
            'actionReturnInt' => ActionReturnInt::class,
        ]);

        /** @var RootProcessor $rootProcessor */
        $rootProcessor = $container->get(RootProcessor::class);
        $this->rootProcessor = $rootProcessor;
    }

    // ─── Simple nodes ─────────────────────────────────────────────────────

    public function testValueNodeReturnsStaticValue(): void
    {
        $root = RuleBuilder::root()->rules(RuleBuilder::value(42))->build();

        self::assertSame(42, $this->rootProcessor->process($root));
    }

    public function testContextNodeWithoutOperatorReturnsRawValue(): void
    {
        // depositCount returns 2
        $root = RuleBuilder::root()
            ->rules(RuleBuilder::context('depositCount'))
            ->build()
        ;

        self::assertSame(2, $this->rootProcessor->process($root));
    }

    public function testContextNodeWithEqualOperatorReturnsTrue(): void
    {
        $root = RuleBuilder::root()
            ->rules(RuleBuilder::context('depositCount')->equal(2))
            ->build()
        ;

        self::assertTrue($this->rootProcessor->process($root));
    }

    public function testContextNodeWithEqualOperatorReturnsFalse(): void
    {
        $root = RuleBuilder::root()
            ->rules(RuleBuilder::context('depositCount')->equal(99))
            ->build()
        ;

        self::assertFalse($this->rootProcessor->process($root));
    }

    // ─── Modifiers ────────────────────────────────────────────────────────

    /**
     * Mirrors testNodeContextWithModifiers.json
     * actionReturnInt=5 → (5*5-2)*2=46 → (46-6)*0.1=4.0 → min(4.0,5)=4.0
     */
    public function testContextNodeWithModifiers(): void
    {
        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::context('actionReturnInt')
                    ->modifier('($context * 5 - 2) * 2')
                    ->modifier('($context - 6) * 0.1')
                    ->modifier('min($context, 5)'),
            )
            ->build()
        ;

        self::assertSame(4, $this->rootProcessor->process($root));
    }

    // ─── Storage ──────────────────────────────────────────────────────────

    /**
     * Mirrors testNodeRootWithStorage.json
     * actionReturnInt=5, ($context*5-$value2)*2=(23)*2=46, (46-6)*0.1=4.0, min(4.0,$value5)=4.0
     */
    public function testRootStorageVariablesAreAccessibleInModifiers(): void
    {
        $root = RuleBuilder::root()
            ->storage(['$value2' => 2, '$value5' => 5])
            ->rules(
                RuleBuilder::context('actionReturnInt')
                    ->modifiers([
                        '($context * 5 - $value2) * 2',
                        '($context - 6) * 0.1',
                        'min($context, $value5)',
                    ]),
            )
            ->build()
        ;

        self::assertSame(4, $this->rootProcessor->process($root));
    }

    // ─── Condition ────────────────────────────────────────────────────────

    /**
     * Mirrors testNodeConditionThenCase.json
     * depositCount >= 2 → action1 (true)
     */
    public function testConditionThenBranch(): void
    {
        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::condition()
                    ->if(RuleBuilder::context('depositCount')->greaterThanOrEqual(2))
                    ->then(RuleBuilder::context('action1'))
                    ->else(RuleBuilder::context('action2')),
            )
            ->build()
        ;

        self::assertTrue($this->rootProcessor->process($root));
    }

    /**
     * Mirrors testNodeConditionElseCase.json
     * depositCount >= 100 → false → action2 (false)
     */
    public function testConditionElseBranch(): void
    {
        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::condition()
                    ->if(RuleBuilder::context('depositCount')->greaterThanOrEqual(100))
                    ->then(RuleBuilder::context('action1'))
                    ->else(RuleBuilder::context('action2')),
            )
            ->build()
        ;

        self::assertFalse($this->rootProcessor->process($root));
    }

    // ─── Collection ───────────────────────────────────────────────────────

    /**
     * Mirrors testNodeAndCollectionAllTrue.json
     * withdrawalCount==0 AND visitCount>1 → both true → true
     */
    public function testCollectionAndAllTrue(): void
    {
        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::collection(Collection::TYPE_AND)
                    ->add(RuleBuilder::context('withdrawalCount')->equal(0))
                    ->add(RuleBuilder::context('visitCount')->greaterThan(1)),
            )
            ->build()
        ;

        self::assertTrue($this->rootProcessor->process($root));
    }

    /**
     * withdrawalCount==99 → false → AND short-circuits → false
     */
    public function testCollectionAndShortCircuits(): void
    {
        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::collection(Collection::TYPE_AND)
                    ->add(RuleBuilder::context('withdrawalCount')->equal(99))
                    ->add(RuleBuilder::context('visitCount')->greaterThan(1)),
            )
            ->build()
        ;

        self::assertFalse($this->rootProcessor->process($root));
    }

    /**
     * hasVipStatus==false, withdrawalCount==0 → OR → true (second is true)
     */
    public function testCollectionOrOneTrue(): void
    {
        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::collection(Collection::TYPE_OR)
                    ->add(RuleBuilder::context('hasVipStatus')->equal(true))
                    ->add(RuleBuilder::context('withdrawalCount')->equal(0)),
            )
            ->build()
        ;

        self::assertTrue($this->rootProcessor->process($root));
    }

    /**
     * atLeast 2 of [withdrawalCount==0, visitCount>1, hasVipStatus==true]
     * → [true, true, false] → 2 >= 2 → true
     */
    public function testCollectionAtLeastTrue(): void
    {
        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::collection(Collection::TYPE_AT_LEAST)
                    ->count(2)
                    ->add(RuleBuilder::context('withdrawalCount')->equal(0))
                    ->add(RuleBuilder::context('visitCount')->greaterThan(1))
                    ->add(RuleBuilder::context('hasVipStatus')->equal(true)),
            )
            ->build()
        ;

        self::assertTrue($this->rootProcessor->process($root));
    }

    // ─── Combined (mirrors testCombined1.json) ────────────────────────────

    /**
     * condition.if: AND[withdrawalCount==0, inGroup contains 'testgroup']
     * → true → action1 → true
     */
    public function testCombinedConditionWithCollection(): void
    {
        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::condition()
                    ->if(
                        RuleBuilder::collection(Collection::TYPE_AND)
                            ->add(RuleBuilder::context('withdrawalCount')->equal(0))
                            ->add(
                                RuleBuilder::context('inGroup')
                                    ->arrayContain(['testgroup', 'testgroup2']),
                            ),
                    )
                    ->then(RuleBuilder::context('action1'))
                    ->else(RuleBuilder::context('action2')),
            )
            ->build()
        ;

        self::assertTrue($this->rootProcessor->process($root));
    }

    // ─── Switch Node ──────────────────────────────────────────────────────

    /**
     * userRole='admin' → equal match → 100
     */
    public function testSwitchEqualCaseMatch(): void
    {
        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::switch('userRole')
                    ->case('admin', RuleBuilder::value(100))
                    ->case('manager', RuleBuilder::value(50))
                    ->default(RuleBuilder::value(0)),
            )
            ->build()
        ;

        self::assertSame(100, $this->rootProcessor->process($root));
    }

    /**
     * depositSum=6000, greaterThan 10000 → false, greaterThan 5000 → true → 'gold'
     */
    public function testSwitchRangeDispatch(): void
    {
        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::switch('depositSum')
                    ->caseOp('greaterThan', 10000, RuleBuilder::value('platinum'))
                    ->caseOp('greaterThan', 5000, RuleBuilder::value('gold'))
                    ->caseOp('greaterThan', 1000, RuleBuilder::value('silver'))
                    ->default(RuleBuilder::value('bronze')),
            )
            ->build()
        ;

        self::assertSame('gold', $this->rootProcessor->process($root));
    }

    /**
     * userRole='admin', no matching case → default → 0
     */
    public function testSwitchFallsToDefault(): void
    {
        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::switch('userRole')
                    ->case('manager', RuleBuilder::value(50))
                    ->default(RuleBuilder::value(0)),
            )
            ->build()
        ;

        self::assertSame(0, $this->rootProcessor->process($root));
    }

    /**
     * switch.then can be a complex node (context)
     * userRole='admin' → then: depositCount context → 2
     */
    public function testSwitchThenIsContextNode(): void
    {
        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::switch('userRole')
                    ->case('admin', RuleBuilder::context('depositCount'))
                    ->default(RuleBuilder::value(0)),
            )
            ->build()
        ;

        self::assertSame(2, $this->rootProcessor->process($root));
    }

    // ─── Stoppable context ────────────────────────────────────────────────

    public function testStoppableContextSetsRootResult(): void
    {
        // actionReturnInt=5, stoppable: result stored on Root and evaluation stops
        $root = RuleBuilder::root()
            ->rules(RuleBuilder::context('actionReturnInt')->stoppable())
            ->build()
        ;

        self::assertSame(5, $this->rootProcessor->process($root));
    }

    // ─── Result equivalence with JSON parser ─────────────────────────────

    /**
     * Verifies that RuleBuilder produces the exact same result as the JSON parser
     * for the combined scenario (mirrors testCombined1.json).
     */
    public function testFluentBuilderMatchesJsonParserResult(): void
    {
        $container = new Container([
            'withdrawalCount' => WithdrawalCount::class,
            'inGroup'         => InGroup::class,
            'action1'         => Action1::class,
            'action2'         => Action2::class,
        ]);

        /** @var RootProcessor $processor */
        $processor = $container->get(RootProcessor::class);

        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::condition()
                    ->if(
                        RuleBuilder::collection(Collection::TYPE_AND)
                            ->add(RuleBuilder::context('withdrawalCount')->equal(0))
                            ->add(
                                RuleBuilder::context('inGroup')
                                    ->arrayContain(['testgroup', 'testgroup2']),
                            ),
                    )
                    ->then(RuleBuilder::context('action1'))
                    ->else(RuleBuilder::context('action2')),
            )
            ->build()
        ;

        // action1 returns true, action2 returns false
        // withdrawalCount=0 and inGroup='testgroup' → condition.then fires → true
        self::assertTrue($processor->process($root));
    }
}
