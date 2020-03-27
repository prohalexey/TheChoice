<?php

namespace TheChoice\Tests\Integration;

use \PHPUnit\Framework\TestCase;

use TheChoice\Builder\JsonBuilder;
use TheChoice\Container;
use TheChoice\Processor\RootProcessor;

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
     * @var RootProcessor
     */
    private $rootProcessor;
    
    private $testFilesDir;

    public function setUp()
    {
        parent::setUp();

        $this->testFilesDir = '';
        if (basename(getcwd()) === 'TheChoice') {
            $this->testFilesDir = './test/Integration/';
        }

        $container = new Container([
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
        ]);
        
        $this->parser = $container->get(JsonBuilder::class);
        $this->rootProcessor = $container->get(RootProcessor::class);
    }

    /**
     * @test
     */
    public function NodeContextWithOperatorArrayContainTestXX()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorArrayContain.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function NodeContextWithOperatorArrayContainTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorArrayContain.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorArrayNotContainTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorArrayNotContain.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorEqualTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorEqual.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorEqualAndContextWithParamsTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorEqualAndContextWithParams.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorGreaterThanTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorGreaterThan.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorGreaterThanOrEqualTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorGreaterThanOrEqual.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorLowerThanTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorLowerThan.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }


    /**
     * @test
     */
    public function nodeContextWithOperatorLowerThanOrEqualTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorLowerThanOrEqual.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorNotEqualTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorNotEqual.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorStringContainTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorStringContain.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithOperatorStringNotContainTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorStringNotContain.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextResultTrueTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextResultTrue.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextResultFalseTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextResultFalse.json');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeContextWithParamsTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithParams.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextWithModifiersTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithModifiers.json');
        $result = $this->rootProcessor->process($node);
        self::assertSame(4, $result);
    }

    /**
     * @test
     */
    public function nodeContextWithModifiersAndOperatorTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithModifiersAndOperator.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeContextStoppableTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextStoppable.json');
        $result = $this->rootProcessor->process($node);
        self::assertSame(5, $result);
    }

    /**
     * @test
     */
    public function nodeConditionThenCaseTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeConditionThenCase.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeConditionElseCaseTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeConditionElseCase.json');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeAndCollectionAllFalseTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeAndCollectionAllFalse.json');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeAndCollectionOneFalseTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeAndCollectionOneFalse.json');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeAndCollectionAllTrueTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeAndCollectionAllTrue.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeOrCollectionAllFalseTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeOrCollectionAllFalse.json');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function nodeOrCollectionOneFalseTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeOrCollectionOneTrue.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeOrCollectionAllTrueTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeOrCollectionAllTrue.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function combined1Test()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testCombined1.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function nodeValueTest()
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeValue.json');
        $result = $this->rootProcessor->process($node);
        self::assertSame(4, $result);
    }
}