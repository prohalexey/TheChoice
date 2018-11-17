<?php

use \PHPUnit\Framework\TestCase;

use TheChoice\ {
    Factory\ActionContextFactory,
    Factory\RuleContextFactory,
    Factory\OperatorFactory,
    TreeProcessor,
    Builder\JsonBuilder
};

final class jsonTest extends TestCase
{
    /**
     * @var JsonBuilder
     */
    private $parser;

    /**
     * @var TreeProcessor
     */
    private $treeProcessor;

    public function setUp()
    {
        parent::setUp();

        require_once './Rules/VisitCount.php';
        require_once './Rules/HasVipStatus.php';
        require_once './Rules/InGroup.php';
        require_once './Rules/WithdrawalCount.php';
        require_once './Rules/DepositCount.php';
        require_once './Rules/UtmSource.php';

        require_once './Actions/Action1.php';
        require_once './Actions/Action2.php';
        require_once './Actions/ActionBreak.php';

        $this->parser = new JsonBuilder(new OperatorFactory());

        $ruleContextFactory = new RuleContextFactory([
            'visitCount' => VisitCount::class,
            'hasVipStatus' => HasVipStatus::class,
            'inGroup' => InGroup::class,
            'withdrawalCount' => WithdrawalCount::class,
            'depositCount' => DepositCount::class,
            'utmSource' => UtmSource::class,
        ]);

        $actionContextFactory = new ActionContextFactory([
            'action1' => Action1::class,
            'action2' => Action2::class,
            'actionBreak' => ActionBreak::class,
        ]);

        $this->treeProcessor = new TreeProcessor($ruleContextFactory, $actionContextFactory);
    }

    /**
     * @test
     */
    public function OneNodeWithRuleArrayContainTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleArrayContain.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleArrayNotContainTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleArrayNotContain.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleEqualTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleEqual.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleGreaterThanTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleGreaterThan.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleGreaterThanOrEqualTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleGreaterThanOrEqual.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleLowerThanTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleLowerThan.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }


    /**
     * @test
     */
    public function oneNodeWithRuleLowerThanOrEqualTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleLowerThanOrEqual.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleNotEqualTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleNotEqual.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleStringContainTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleStringContain.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithRuleStringNotContainTest()
    {
        $node = $this->parser->parseFile('Json/testOneNodeWithRuleStringNotContain.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithActionResultTrueTest()
    {
        $node = $this->parser->parseFile('Json/testNodeActionResultTrue.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function oneNodeWithActionResultFalseTest()
    {
        $node = $this->parser->parseFile('Json/testNodeActionResultFalse.json');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function oneNodeWithActionStoppableTest()
    {
        $node = $this->parser->parseFile('Json/testNodeActionStoppable.json');
        $result = $this->treeProcessor->process($node);
        self::assertEquals(5, $result);
    }

    /**
     * @test
     */
    public function nodeConditionThenCaseTest()
    {
        $node = $this->parser->parseFile('Json/testNodeConditionThenCase.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeConditionElseCaseTest()
    {
        $node = $this->parser->parseFile('Json/testNodeConditionElseCase.json');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeAndCollectionAllFalseTest()
    {
        $node = $this->parser->parseFile('Json/testNodeAndCollectionAllFalse.json');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeAndCollectionOneFalseTest()
    {
        $node = $this->parser->parseFile('Json/testNodeAndCollectionOneFalse.json');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeAndCollectionAllTrueTest()
    {
        $node = $this->parser->parseFile('Json/testNodeAndCollectionAllTrue.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeOrCollectionAllFalseTest()
    {
        $node = $this->parser->parseFile('Json/testNodeOrCollectionAllFalse.json');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeOrCollectionOneFalseTest()
    {
        $node = $this->parser->parseFile('Json/testNodeOrCollectionOneTrue.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeOrCollectionAllTrueTest()
    {
        $node = $this->parser->parseFile('Json/testNodeOrCollectionAllTrue.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function combined1Test()
    {
        $node = $this->parser->parseFile('Json/testCombined1.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }
}