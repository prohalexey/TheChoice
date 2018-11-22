<?php

namespace TheChoice\Tests\Integration;

use \PHPUnit\Framework\TestCase;

use TheChoice\ {
    Factory\ContextFactory,
    Factory\OperatorFactory,
    TreeProcessor,
    Builder\JsonBuilder
};

use TheChoice\Tests\Integration\Contexts\ {
    VisitCount,
    HasVipStatus,
    InGroup,
    WithdrawalCount,
    DepositCount,
    UtmSource,
    ContextWithParams,
    Action1,
    Action2,
    ActionBreak,
    ActionWithParams
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

        $this->parser = new JsonBuilder(new OperatorFactory());

        $this->treeProcessor = (new TreeProcessor())->setContextFactory(
            new ContextFactory([
                'visitCount' => VisitCount::class,
                'hasVipStatus' => HasVipStatus::class,
                'inGroup' => InGroup::class,
                'withdrawalCount' => WithdrawalCount::class,
                'depositCount' => DepositCount::class,
                'utmSource' => UtmSource::class,
                'contextWithParams' => ContextWithParams::class,
                'action1' => Action1::class,
                'action2' => Action2::class,
                'actionBreak' => ActionBreak::class,
                'actionWithParams' => ActionWithParams::class,
            ])
        );
    }

    /**
     * @test
     */
    public function NodeContextWithOperatorArrayContainTest()
    {
        $node = $this->parser->parseFile('Json/testNodeContextWithOperatorArrayContain.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorArrayNotContainTest()
    {
        $node = $this->parser->parseFile('Json/testNodeContextWithOperatorArrayNotContain.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorEqualTest()
    {
        $node = $this->parser->parseFile('Json/testNodeContextWithOperatorEqual.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorEqualAndContextWithParamsTest()
    {
        $node = $this->parser->parseFile('Json/testNodeContextWithOperatorEqualAndContextWithParams.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorGreaterThanTest()
    {
        $node = $this->parser->parseFile('Json/testNodeContextWithOperatorGreaterThan.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorGreaterThanOrEqualTest()
    {
        $node = $this->parser->parseFile('Json/testNodeContextWithOperatorGreaterThanOrEqual.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorLowerThanTest()
    {
        $node = $this->parser->parseFile('Json/testNodeContextWithOperatorLowerThan.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }


    /**
     * @test
     */
    public function nodeContextWithOperatorLowerThanOrEqualTest()
    {
        $node = $this->parser->parseFile('Json/testNodeContextWithOperatorLowerThanOrEqual.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorNotEqualTest()
    {
        $node = $this->parser->parseFile('Json/testNodeContextWithOperatorNotEqual.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorStringContainTest()
    {
        $node = $this->parser->parseFile('Json/testNodeContextWithOperatorStringContain.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorStringNotContainTest()
    {
        $node = $this->parser->parseFile('Json/testNodeContextWithOperatorStringNotContain.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithActionResultTrueTest()
    {
        $node = $this->parser->parseFile('Json/testNodeActionResultTrue.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithActionResultFalseTest()
    {
        $node = $this->parser->parseFile('Json/testNodeActionResultFalse.json');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeContextWithActionWithParamsTest()
    {
        $node = $this->parser->parseFile('Json/testNodeActionWithParams.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithActionStoppableTest()
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