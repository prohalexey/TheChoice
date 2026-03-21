<?php

namespace TheChoice\Tests\Integration;

use TheChoice\Builder\JsonBuilder;

final class jsonTest extends AbstractFormatIntegrationTestCase
{
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

    public function testNodeContextWithOperatorStartsWithTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorStartsWith.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorEndsWithTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorEndsWith.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorMatchesRegexTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorMatchesRegex.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorIsEmptyTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorIsEmpty.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorContainsKeyTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorContainsKey.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorCountEqualTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorCountEqual.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithOperatorCountGreaterThanTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorCountGreaterThan.json');
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

    public function testNodeRootWithStorageTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeRootWithStorage.json');
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

    public function testNodeOrCollectionOneTrueTest(): void
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

    public function testNodeNotCollectionTrueTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeNotCollectionTrue.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeNotCollectionFalseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeNotCollectionFalse.json');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    public function testNodeNotCollectionMultipleTrueTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeNotCollectionMultipleTrue.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeAtLeastCollectionTrueTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeAtLeastCollectionTrue.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeAtLeastCollectionFalseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeAtLeastCollectionFalse.json');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
    }

    public function testNodeExactlyCollectionTrueTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeExactlyCollectionTrue.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeExactlyCollectionFalseTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeExactlyCollectionFalse.json');
        $result = $this->rootProcessor->process($node);
        self::assertFalse($result);
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

    public function testNodeSwitchEqualMatchTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeSwitchEqualMatch.json');
        $result = $this->rootProcessor->process($node);
        self::assertSame(100, $result);
    }

    public function testNodeSwitchOperatorMatchTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeSwitchOperatorMatch.json');
        $result = $this->rootProcessor->process($node);
        self::assertSame('gold', $result);
    }

    public function testNodeSwitchDefaultFallbackTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeSwitchDefaultFallback.json');
        $result = $this->rootProcessor->process($node);
        self::assertSame(0, $result);
    }

    public function testNodeSwitchNoDefaultTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeSwitchNoDefault.json');
        $result = $this->rootProcessor->process($node);
        self::assertNull($result);
    }

    public function testNodeSwitchEmptyCasesTest(): void
    {
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeSwitchEmptyCases.json');
        $result = $this->rootProcessor->process($node);
        self::assertSame(999, $result);
    }

    public function testNodeSwitchComplexThenTest(): void
    {
        // userRole='admin' matches, then returns depositCount context value (2)
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeSwitchComplexThen.json');
        $result = $this->rootProcessor->process($node);
        self::assertSame(2, $result);
    }

    // ─── Storage variables in operator values (#13) ───────────────────────

    public function testNodeContextWithStorageValueTest(): void
    {
        // depositCount=2, $expectedCount=2 → equal → true
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithStorageValue.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeContextWithStorageValueOperatorTest(): void
    {
        // visitCount=2, $minVisits=1 → greaterThan → true
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeContextWithStorageValueOperator.json');
        $result = $this->rootProcessor->process($node);
        self::assertTrue($result);
    }

    public function testNodeSwitchWithStorageValueTest(): void
    {
        // userRole='admin', $adminRole='admin' → case matches → 100
        $node = $this->parser->parseFile($this->testFilesDir . 'Json/testNodeSwitchWithStorageValue.json');
        $result = $this->rootProcessor->process($node);
        self::assertSame(100, $result);
    }

    protected function getBuilderClass(): string
    {
        return JsonBuilder::class;
    }
}
