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
    ActionReturnInt,
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
    
    private $testFilesDir;

    public function setUp()
    {
        parent::setUp();

        $this->testFilesDir = '';
        if (basename(getcwd()) === 'TheChoice') {
            $this->testFilesDir = './test/Integration/';
        }
        
        $this->parser = new JsonBuilder(new OperatorFactory());
        $this->parser->setRootDir($this->testFilesDir . 'Json/');

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
                'actionReturnInt' => ActionReturnInt::class,
                'actionWithParams' => ActionWithParams::class,
            ])
        );
    }

    /**
     * @test
     */
    public function NodeContextWithOperatorArrayContainTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorArrayContain.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorArrayNotContainTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorArrayNotContain.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorEqualTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorEqual.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorEqualAndContextWithParamsTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorEqualAndContextWithParams.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorGreaterThanTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorGreaterThan.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorGreaterThanOrEqualTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorGreaterThanOrEqual.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorLowerThanTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorLowerThan.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }


    /**
     * @test
     */
    public function nodeContextWithOperatorLowerThanOrEqualTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorLowerThanOrEqual.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorNotEqualTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorNotEqual.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorStringContainTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorStringContain.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorStringNotContainTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorStringNotContain.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextResultTrueTest()
    {
        $node = $this->parser->parseFile('testNodeContextResultTrue.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextResultFalseTest()
    {
        $node = $this->parser->parseFile('testNodeContextResultFalse.json');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeContextWithParamsTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithParams.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithModifiersTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithModifiers.json');
        $result = $this->treeProcessor->process($node);
        self::assertSame(4, $result);
    }

    /**
     * @test
     */
    public function nodeContextWithModifiersAndOperatorTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithModifiersAndOperator.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextStoppableTest()
    {
        $node = $this->parser->parseFile('testNodeContextStoppable.json');
        $result = $this->treeProcessor->process($node);
        self::assertSame(5, $result);
    }

    /**
     * @test
     */
    public function nodeConditionThenCaseTest()
    {
        $node = $this->parser->parseFile('testNodeConditionThenCase.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeConditionElseCaseTest()
    {
        $node = $this->parser->parseFile('testNodeConditionElseCase.json');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeAndCollectionAllFalseTest()
    {
        $node = $this->parser->parseFile('testNodeAndCollectionAllFalse.json');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeAndCollectionOneFalseTest()
    {
        $node = $this->parser->parseFile('testNodeAndCollectionOneFalse.json');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeAndCollectionAllTrueTest()
    {
        $node = $this->parser->parseFile('testNodeAndCollectionAllTrue.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeOrCollectionAllFalseTest()
    {
        $node = $this->parser->parseFile('testNodeOrCollectionAllFalse.json');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeOrCollectionOneFalseTest()
    {
        $node = $this->parser->parseFile('testNodeOrCollectionOneTrue.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeOrCollectionAllTrueTest()
    {
        $node = $this->parser->parseFile('testNodeOrCollectionAllTrue.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function combined1Test()
    {
        $node = $this->parser->parseFile('testCombined1.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeRequireTest()
    {
        $node = $this->parser->parseFile('testRequireLoop1.json');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeRequireCircularLoopTest()
    {
        $this->expectException(\RuntimeException::class);
        $this->parser->parseFile('testRequireCircularLoop1.json');
    }

    /**
     * @test
     */
    public function nodeValueTest()
    {
        $node = $this->parser->parseFile('testNodeValue.json');
        $result = $this->treeProcessor->process($node);
        self::assertSame(4, $result);
    }
}