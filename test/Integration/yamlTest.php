<?php

namespace TheChoice\Tests\Integration;

use TheChoice\Builder\YamlBuilder;

final class yamlTest extends AbstractFormatIntegrationTestCase
{
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

    public function testNodeAssertStoppableTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeAssertStoppable.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
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

    public function testNodeOrCollectionOneTrueTest(): void
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

    public function testNodeNotCollectionTrueTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeNotCollectionTrue.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeNotCollectionFalseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeNotCollectionFalse.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    public function testNodeNotCollectionMultipleTrueTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeNotCollectionMultipleTrue.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeAtLeastCollectionTrueTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeAtLeastCollectionTrue.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeAtLeastCollectionFalseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeAtLeastCollectionFalse.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    public function testNodeExactlyCollectionTrueTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeExactlyCollectionTrue.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeExactlyCollectionFalseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Yaml/testNodeExactlyCollectionFalse.yaml');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
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

    protected function getBuilderClass(): string
    {
        return YamlBuilder::class;
    }
}
