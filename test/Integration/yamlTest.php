<?php

namespace TheChoice\Tests\Integration;

use PHPUnit\Framework\TestCase;
use TheChoice\Builder\YamlBuilder;
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

final class yamlTest extends TestCase
{
    private YamlBuilder $parser;

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

        $this->parser = $container->get(YamlBuilder::class);
        $this->rootProcessor = $container->get(RootProcessor::class);
    }

    public function testNodeContextWithOperatorArrayContainTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeContextWithOperatorArrayContain.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorArrayNotContainTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeContextWithOperatorArrayNotContain.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorEqualTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeContextWithOperatorEqual.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorEqualAndContextWithParamsTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeContextWithOperatorEqualAndContextWithParams.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorGreaterThanTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeContextWithOperatorGreaterThan.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorGreaterThanOrEqualTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeContextWithOperatorGreaterThanOrEqual.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorLowerThanTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeContextWithOperatorLowerThan.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorLowerThanOrEqualTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeContextWithOperatorLowerThanOrEqual.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorNotEqualTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeContextWithOperatorNotEqual.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorStringContainTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeContextWithOperatorStringContain.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorStringNotContainTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeContextWithOperatorStringNotContain.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextResultTrueTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeContextResultTrue.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextResultFalseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeContextResultFalse.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    public function testNodeContextWithParamsTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeContextWithParams.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithModifiersTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeContextWithModifiers.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertSame(4, $result);
    }

    public function testNodeRootWithStorageTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeRootWithStorage.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertSame(4, $result);
    }

    public function testNodeContextWithModifiersAndOperatorTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeContextWithModifiersAndOperator.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextStoppableTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeContextStoppable.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertSame(5, $result);
    }

    public function testNodeConditionThenCaseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeConditionThenCase.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeConditionElseCaseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeConditionElseCase.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    public function testNodeAndCollectionAllFalseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeAndCollectionAllFalse.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    public function testNodeAndCollectionOneFalseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeAndCollectionOneFalse.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    public function testNodeAndCollectionAllTrueTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeAndCollectionAllTrue.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeOrCollectionAllFalseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeOrCollectionAllFalse.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    public function testNodeOrCollectionOneFalseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeOrCollectionOneTrue.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeOrCollectionAllTrueTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeOrCollectionAllTrue.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testCombined1Test(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testCombined1.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeValueTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeValue.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertSame(4, $result);
    }
}
