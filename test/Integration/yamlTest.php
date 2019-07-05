<?php

namespace TheChoice\Tests\Integration;

use \PHPUnit\Framework\TestCase;

use TheChoice\ {
    Factory\ContextFactory,
    Factory\OperatorFactory,
    TreeProcessor,
    Builder\YamlBuilder
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

final class yamlTest extends TestCase
{
    /**
     * @var YamlBuilder
     */
    private $parser;

    /**
     * @var TreeProcessor
     */
    private $treeProcessor;

    private $testFilesDir;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->testFilesDir = '';
        if (basename(getcwd()) === 'TheChoice') {
            $this->testFilesDir = './test/Integration/';
        }
        
        $this->parser = new YamlBuilder(new OperatorFactory());
        $this->parser->setRootDir($this->testFilesDir . 'Yaml/');

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
        $node = $this->parser->parseFile('testNodeContextWithOperatorArrayContain.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorArrayNotContainTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorArrayNotContain.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorEqualTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorEqual.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorEqualAndContextWithParamsTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorEqualAndContextWithParams.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorGreaterThanTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorGreaterThan.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorGreaterThanOrEqualTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorGreaterThanOrEqual.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorLowerThanTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorLowerThan.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }


    /**
     * @test
     */
    public function nodeContextWithOperatorLowerThanOrEqualTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorLowerThanOrEqual.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorNotEqualTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorNotEqual.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorStringContainTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorStringContain.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorStringNotContainTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithOperatorStringNotContain.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }
    
    /**
     * @test
     */
    public function nodeContextResultTrueTest()
    {
        $node = $this->parser->parseFile('testNodeContextResultTrue.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextResultFalseTest()
    {
        $node = $this->parser->parseFile('testNodeContextResultFalse.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeContextWithParamsTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithParams.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithModifiersTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithModifiers.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertSame(4, $result);
    }

    /**
     * @test
     */
    public function nodeTreeWithStorageTest()
    {
        $node = $this->parser->parseFile('testNodeTreeWithStorage.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertSame(4, $result);
    }

    /**
     * @test
     */
    public function nodeContextWithModifiersAndOperatorTest()
    {
        $node = $this->parser->parseFile('testNodeContextWithModifiersAndOperator.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextStoppableTest()
    {
        $node = $this->parser->parseFile('testNodeContextStoppable.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertSame(5, $result);
    }

    /**
     * @test
     */
    public function nodeConditionThenCaseTest()
    {
        $node = $this->parser->parseFile('testNodeConditionThenCase.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeConditionElseCaseTest()
    {
        $node = $this->parser->parseFile('testNodeConditionElseCase.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeAndCollectionAllFalseTest()
    {
        $node = $this->parser->parseFile('testNodeAndCollectionAllFalse.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeAndCollectionOneFalseTest()
    {
        $node = $this->parser->parseFile('testNodeAndCollectionOneFalse.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeAndCollectionAllTrueTest()
    {
        $node = $this->parser->parseFile('testNodeAndCollectionAllTrue.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeOrCollectionAllFalseTest()
    {
        $node = $this->parser->parseFile('testNodeOrCollectionAllFalse.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeOrCollectionOneFalseTest()
    {
        $node = $this->parser->parseFile('testNodeOrCollectionOneTrue.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeOrCollectionAllTrueTest()
    {
        $node = $this->parser->parseFile('testNodeOrCollectionAllTrue.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function combined1Test()
    {
        $node = $this->parser->parseFile('testCombined1.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeRequireTest()
    {
        $node = $this->parser->parseFile('testRequireLoop1.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeRequireCircularLoopTest()
    {
        $this->expectException(\RuntimeException::class);
        $this->parser->parseFile('testRequireCircularLoop1.yaml');
    }


    /**
     * @test
     */
    public function nodeValueTest()
    {
        $node = $this->parser->parseFile('testNodeValue.yaml');
        $result = $this->treeProcessor->process($node);
        self::assertSame(4, $result);
    }
}