<?php

namespace TheChoice\Tests\Integration;

use PHPUnit\Framework\TestCase;
use TheChoice\Builder\JsonBuilder;
use TheChoice\Container;
use TheChoice\Processor\RootProcessor;
use TheChoice\Tests\Integration\Contexts\Action1;
use TheChoice\Tests\Integration\Contexts\Action2;
use TheChoice\Tests\Integration\Contexts\ActionReturnInt;
use TheChoice\Tests\Integration\Contexts\ActionWithParams;
use TheChoice\Tests\Integration\Contexts\ContextWithParams;
use TheChoice\Tests\Integration\Contexts\DepositCount;
use TheChoice\Tests\Integration\Contexts\HasVipStatus;
use TheChoice\Tests\Integration\Contexts\InGroup;
use TheChoice\Tests\Integration\Contexts\UtmSource;
use TheChoice\Tests\Integration\Contexts\VisitCount;
use TheChoice\Tests\Integration\Contexts\WithdrawalCount;

final class jsonTest extends TestCase
{
    private JsonBuilder $parser;

    private RootProcessor $rootProcessor;

    private string $testFilesDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testFilesDir = '';
        if ('TheChoice' === basename(getcwd())) {
            $this->testFilesDir = './test/Integration/';
        }

        $container = new Container([
            'visitCount'        => VisitCount::class,
            'hasVipStatus'      => HasVipStatus::class,
            'inGroup'           => InGroup::class,
            'withdrawalCount'   => WithdrawalCount::class,
            'depositCount'      => DepositCount::class,
            'utmSource'         => UtmSource::class,
            'contextWithParams' => ContextWithParams::class,
            'action1'           => Action1::class,
            'action2'           => Action2::class,
            'actionReturnInt'   => ActionReturnInt::class,
            'actionWithParams'  => ActionWithParams::class,
        ]);

        $this->parser = $container->get(JsonBuilder::class);
        $this->rootProcessor = $container->get(RootProcessor::class);
    }

    public function testNodeContextWithOperatorArrayContainTestXX(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorArrayContain.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorArrayContainTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorArrayContain.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorArrayNotContainTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorArrayNotContain.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorEqualTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorEqual.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorEqualAndContextWithParamsTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorEqualAndContextWithParams.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorGreaterThanTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorGreaterThan.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorGreaterThanOrEqualTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorGreaterThanOrEqual.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorLowerThanTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorLowerThan.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorLowerThanOrEqualTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorLowerThanOrEqual.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorNotEqualTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorNotEqual.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorStringContainTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorStringContain.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorStringNotContainTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorStringNotContain.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextResultTrueTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextResultTrue.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextResultFalseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextResultFalse.json');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    public function testNodeContextWithParamsTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithParams.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithModifiersTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithModifiers.json');
        $result = $this->rootProcessor->process($node);
        self::assertSame(4, $result);
    }

    public function testNodeContextWithModifiersAndOperatorTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithModifiersAndOperator.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextStoppableTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextStoppable.json');
        $result = $this->rootProcessor->process($node);
        self::assertSame(5, $result);
    }

    public function testNodeConditionThenCaseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeConditionThenCase.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeConditionElseCaseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeConditionElseCase.json');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    public function testNodeAndCollectionAllFalseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeAndCollectionAllFalse.json');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    public function testNodeAndCollectionOneFalseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeAndCollectionOneFalse.json');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    public function testNodeAndCollectionAllTrueTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeAndCollectionAllTrue.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeOrCollectionAllFalseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeOrCollectionAllFalse.json');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    public function testNodeOrCollectionOneFalseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeOrCollectionOneTrue.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeOrCollectionAllTrueTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeOrCollectionAllTrue.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testCombined1Test(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testCombined1.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeValueTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeValue.json');
        $result = $this->rootProcessor->process($node);
        self::assertSame(4, $result);
    }
}
