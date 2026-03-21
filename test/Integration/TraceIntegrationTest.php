<?php

declare(strict_types=1);

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
use TheChoice\Tests\Integration\Contexts\EmptyStringContext;
use TheChoice\Tests\Integration\Contexts\HasVipStatus;
use TheChoice\Tests\Integration\Contexts\InGroup;
use TheChoice\Tests\Integration\Contexts\TagsContext;
use TheChoice\Tests\Integration\Contexts\UtmSource;
use TheChoice\Tests\Integration\Contexts\VisitCount;
use TheChoice\Tests\Integration\Contexts\WithdrawalCount;
use TheChoice\Trace\EvaluationTrace;
use Throwable;

final class TraceIntegrationTest extends TestCase
{
    private JsonBuilder $jsonBuilder;

    private RootProcessor $rootProcessor;

    private string $testFilesDir;

    protected function setUp(): void
    {
        $this->testFilesDir = '';
        if ('TheChoice' === basename((string)getcwd())) {
            $this->testFilesDir = './test/Integration/';
        }

        $container = new Container([
            'visitCount'        => VisitCount::class,
            'hasVipStatus'      => HasVipStatus::class,
            'inGroup'           => InGroup::class,
            'withdrawalCount'   => WithdrawalCount::class,
            'depositCount'      => DepositCount::class,
            'action1'           => Action1::class,
            'action2'           => Action2::class,
            'actionReturnInt'   => ActionReturnInt::class,
            'actionWithParams'  => ActionWithParams::class,
            'contextWithParams' => ContextWithParams::class,
            'utmSource'         => UtmSource::class,
            'emptyString'       => EmptyStringContext::class,
            'tags'              => TagsContext::class,
        ]);

        /** @var JsonBuilder $builder */
        $builder = $container->get(JsonBuilder::class);
        $this->jsonBuilder = $builder;

        /** @var RootProcessor $rootProcessor */
        $rootProcessor = $container->get(RootProcessor::class);
        $this->rootProcessor = $rootProcessor;
    }

    public function testProcessWithTraceReturnsCorrectValueForSimpleContext(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testNodeContextWithOperatorEqual.json');

        $trace = $this->rootProcessor->processWithTrace($node);

        self::assertInstanceOf(EvaluationTrace::class, $trace);
        self::assertTrue($trace->getValue());
    }

    public function testProcessWithTraceReturnsCorrectValueForValue(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testNodeValue.json');

        $trace = $this->rootProcessor->processWithTrace($node);

        self::assertSame(4, $trace->getValue());
    }

    public function testProcessWithTraceOnCombinedRule(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testCombined1.json');

        $trace = $this->rootProcessor->processWithTrace($node);

        self::assertInstanceOf(EvaluationTrace::class, $trace);

        $rootEntry = $trace->getTrace();
        self::assertSame('Root', $rootEntry->getNodeType());
        self::assertNotEmpty($rootEntry->getChildren());
    }

    public function testTraceContainsConditionNodes(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testNodeConditionThenCase.json');

        $trace = $this->rootProcessor->processWithTrace($node);

        $explanation = $trace->explain();

        self::assertStringContainsString('Root', $explanation);
        self::assertStringContainsString('Condition', $explanation);
    }

    public function testTraceContainsCollectionAndContextNodes(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testNodeAndCollectionAllTrue.json');

        $trace = $this->rootProcessor->processWithTrace($node);

        $explanation = $trace->explain();

        self::assertStringContainsString('Collection[and]', $explanation);
        self::assertStringContainsString('Context', $explanation);
    }

    public function testProcessWithTraceProducesSameResultAsProcess(): void
    {
        $testFiles = [
            'testNodeContextWithOperatorEqual.json',
            'testNodeConditionThenCase.json',
            'testNodeConditionElseCase.json',
            'testNodeAndCollectionAllTrue.json',
            'testNodeAndCollectionAllFalse.json',
            'testNodeAndCollectionOneFalse.json',
            'testNodeOrCollectionAllTrue.json',
            'testNodeOrCollectionAllFalse.json',
            'testNodeOrCollectionOneTrue.json',
            'testNodeNotCollectionTrue.json',
            'testNodeNotCollectionFalse.json',
            'testNodeNotCollectionMultipleTrue.json',
            'testNodeAtLeastCollectionTrue.json',
            'testNodeAtLeastCollectionFalse.json',
            'testNodeExactlyCollectionTrue.json',
            'testNodeExactlyCollectionFalse.json',
            'testNodeContextStoppable.json',
            'testNodeContextWithModifiers.json',
            'testNodeContextWithModifiersAndOperator.json',
            'testNodeRootWithStorage.json',
            'testNodeContextWithParams.json',
            'testNodeValue.json',
            'testNodeSwitchTrace.json',
        ];

        foreach ($testFiles as $file) {
            $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/' . $file);
            $normalResult = $this->rootProcessor->process($node);

            // Re-parse to get a clean node (processor caches are flushed on each process call)
            $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/' . $file);
            $traceResult = $this->rootProcessor->processWithTrace($node);

            self::assertSame(
                $normalResult,
                $traceResult->getValue(),
                sprintf('Mismatch for file %s: expected %s, got %s', $file, var_export($normalResult, true), var_export($traceResult->getValue(), true)),
            );
        }
    }

    public function testTraceCollectorIsCleanedUpAfterProcessWithTrace(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testNodeValue.json');

        $this->rootProcessor->processWithTrace($node);

        // After processWithTrace, the trace collector should be cleared (set to null),
        // so a normal process() call should work without any trace overhead
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testNodeValue.json');
        $result = $this->rootProcessor->process($node);

        self::assertSame(4, $result);
    }

    public function testExplainOutputIsNonEmpty(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testCombined1.json');

        $trace = $this->rootProcessor->processWithTrace($node);

        $explanation = $trace->explain();
        self::assertNotEmpty($explanation);
        self::assertStringContainsString('→', $explanation);
    }

    public function testProcessWithTraceStoppableContextSetsRootResult(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testNodeContextStoppable.json');

        $trace = $this->rootProcessor->processWithTrace($node);

        // Stoppable context sets result to Root node (actionReturnInt returns 5)
        self::assertSame(5, $trace->getValue());

        // Trace should contain the stoppable context
        $explanation = $trace->explain();
        self::assertStringContainsString('Context', $explanation);
    }

    public function testProcessWithTraceModifiers(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testNodeContextWithModifiers.json');

        $trace = $this->rootProcessor->processWithTrace($node);

        self::assertSame(4, $trace->getValue());

        $explanation = $trace->explain();
        self::assertStringContainsString('Context[actionReturnInt]', $explanation);
    }

    public function testProcessWithTraceNotCollection(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testNodeNotCollectionTrue.json');

        $trace = $this->rootProcessor->processWithTrace($node);

        self::assertTrue($trace->getValue());
        self::assertStringContainsString('Collection[not]', $trace->explain());
    }

    public function testProcessWithTraceAtLeastCollection(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testNodeAtLeastCollectionTrue.json');

        $trace = $this->rootProcessor->processWithTrace($node);

        self::assertTrue($trace->getValue());
        self::assertStringContainsString('Collection[atLeast]', $trace->explain());
    }

    public function testProcessWithTraceExactlyCollection(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testNodeExactlyCollectionTrue.json');

        $trace = $this->rootProcessor->processWithTrace($node);

        self::assertTrue($trace->getValue());
        self::assertStringContainsString('Collection[exactly]', $trace->explain());
    }

    public function testTraceCollectorIsCleanedUpAfterException(): void
    {
        // Parse invalid JSON that will fail at runtime (unknown context)
        $badJson = '{"node":"context","context":"nonExistentContext","operator":"equal","value":1}';
        $node = $this->jsonBuilder->parse($badJson);

        try {
            $this->rootProcessor->processWithTrace($node);
            self::fail('Expected exception was not thrown');
        } catch (Throwable) {
            // Expected: context "nonExistentContext" is not registered in the container
        }

        // After a failed processWithTrace, the trace collector must be cleaned up,
        // so a normal process() call should still work
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testNodeValue.json');
        $result = $this->rootProcessor->process($node);

        self::assertSame(4, $result);
    }

    public function testProcessWithTraceSwitchNode(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testNodeSwitchTrace.json');

        $trace = $this->rootProcessor->processWithTrace($node);

        self::assertSame('two', $trace->getValue());

        $explanation = $trace->explain();
        self::assertStringContainsString('Switch', $explanation);
    }
}
