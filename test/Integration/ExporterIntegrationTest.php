<?php

declare(strict_types=1);

namespace TheChoice\Tests\Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TheChoice\Builder\JsonBuilder;
use TheChoice\Builder\YamlBuilder;
use TheChoice\Container;
use TheChoice\Exporter\JsonNodeExporter;
use TheChoice\Exporter\NodeSerializer;
use TheChoice\Exporter\YamlNodeExporter;
use TheChoice\Node\Root;
use TheChoice\Processor\RootProcessor;
use TheChoice\Tests\Integration\Contexts\Action1;
use TheChoice\Tests\Integration\Contexts\Action2;
use TheChoice\Tests\Integration\Contexts\ActionReturnInt;
use TheChoice\Tests\Integration\Contexts\ActionWithParams;
use TheChoice\Tests\Integration\Contexts\ContextWithParams;
use TheChoice\Tests\Integration\Contexts\DepositCount;
use TheChoice\Tests\Integration\Contexts\DepositSum;
use TheChoice\Tests\Integration\Contexts\EmptyStringContext;
use TheChoice\Tests\Integration\Contexts\HasVipStatus;
use TheChoice\Tests\Integration\Contexts\InGroup;
use TheChoice\Tests\Integration\Contexts\TagsContext;
use TheChoice\Tests\Integration\Contexts\UserRole;
use TheChoice\Tests\Integration\Contexts\UtmSource;
use TheChoice\Tests\Integration\Contexts\VisitCount;
use TheChoice\Tests\Integration\Contexts\WithdrawalCount;

/**
 * Verifies that NodeSerializer + exporters produce round-trip-safe output:
 *   original result  ==  re-parse(export(original parse)) result
 */
final class ExporterIntegrationTest extends TestCase
{
    private JsonBuilder $jsonBuilder;

    private YamlBuilder $yamlBuilder;

    private RootProcessor $rootProcessor;

    private JsonNodeExporter $jsonExporter;

    private YamlNodeExporter $yamlExporter;

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
            'depositSum'        => DepositSum::class,
            'userRole'          => UserRole::class,
            'utmSource'         => UtmSource::class,
            'contextWithParams' => ContextWithParams::class,
            'action1'           => Action1::class,
            'action2'           => Action2::class,
            'actionReturnInt'   => ActionReturnInt::class,
            'actionWithParams'  => ActionWithParams::class,
            'emptyString'       => EmptyStringContext::class,
            'tags'              => TagsContext::class,
        ]);

        /** @var JsonBuilder $jsonBuilder */
        $jsonBuilder = $container->get(JsonBuilder::class);
        $this->jsonBuilder = $jsonBuilder;

        /** @var YamlBuilder $yamlBuilder */
        $yamlBuilder = $container->get(YamlBuilder::class);
        $this->yamlBuilder = $yamlBuilder;

        /** @var RootProcessor $rootProcessor */
        $rootProcessor = $container->get(RootProcessor::class);
        $this->rootProcessor = $rootProcessor;

        $serializer = new NodeSerializer();
        $this->jsonExporter = new JsonNodeExporter($serializer);
        $this->yamlExporter = new YamlNodeExporter($serializer);
    }

    // ─── JSON roundtrip ───────────────────────────────────────────────────

    #[DataProvider('jsonFixtureProvider')]
    public function testJsonRoundtrip(string $file): void
    {
        $originalNode = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/' . $file);
        $originalResult = $this->rootProcessor->process($originalNode);

        $exportedJson = $this->jsonExporter->export($originalNode);
        $reparsedNode = $this->jsonBuilder->parse($exportedJson);
        $reparsedResult = $this->rootProcessor->process($reparsedNode);

        self::assertSame(
            $originalResult,
            $reparsedResult,
            sprintf('JSON roundtrip failed for "%s"', $file),
        );
    }

    #[DataProvider('jsonFixtureProvider')]
    public function testCompactJsonRoundtrip(string $file): void
    {
        $originalNode = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/' . $file);
        $originalResult = $this->rootProcessor->process($originalNode);

        $compactJson = $this->jsonExporter->export($originalNode, pretty: false);
        $reparsedNode = $this->jsonBuilder->parse($compactJson);
        $reparsedResult = $this->rootProcessor->process($reparsedNode);

        self::assertSame($originalResult, $reparsedResult);
    }

    // ─── YAML roundtrip ───────────────────────────────────────────────────

    #[DataProvider('jsonFixtureProvider')]
    public function testYamlRoundtripFromJsonSource(string $file): void
    {
        $originalNode = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/' . $file);
        $originalResult = $this->rootProcessor->process($originalNode);

        $exportedYaml = $this->yamlExporter->export($originalNode);
        $reparsedNode = $this->yamlBuilder->parse($exportedYaml);
        $reparsedResult = $this->rootProcessor->process($reparsedNode);

        self::assertSame(
            $originalResult,
            $reparsedResult,
            sprintf('YAML roundtrip failed for "%s"', $file),
        );
    }

    // ─── Data provider ────────────────────────────────────────────────────

    /**
     * @return array<array{string}>
     */
    public static function jsonFixtureProvider(): array
    {
        return [
            ['testNodeValue.json'],
            ['testNodeContextResultTrue.json'],
            ['testNodeContextResultFalse.json'],
            ['testNodeContextWithOperatorEqual.json'],
            ['testNodeContextWithOperatorGreaterThan.json'],
            ['testNodeContextWithOperatorGreaterThanOrEqual.json'],
            ['testNodeContextWithOperatorLowerThan.json'],
            ['testNodeContextWithOperatorNotEqual.json'],
            ['testNodeContextWithOperatorStringContain.json'],
            ['testNodeContextWithOperatorStringNotContain.json'],
            ['testNodeContextWithOperatorArrayContain.json'],
            ['testNodeContextWithOperatorArrayNotContain.json'],
            ['testNodeContextWithOperatorContainsKey.json'],
            ['testNodeContextWithOperatorCountEqual.json'],
            ['testNodeContextWithOperatorCountGreaterThan.json'],
            ['testNodeContextWithOperatorIsEmpty.json'],
            ['testNodeContextWithOperatorStartsWith.json'],
            ['testNodeContextWithOperatorEndsWith.json'],
            ['testNodeContextWithOperatorMatchesRegex.json'],
            ['testNodeContextWithModifiers.json'],
            ['testNodeContextWithParams.json'],
            ['testNodeRootWithStorage.json'],
            ['testNodeConditionThenCase.json'],
            ['testNodeConditionElseCase.json'],
            ['testNodeAndCollectionAllTrue.json'],
            ['testNodeAndCollectionAllFalse.json'],
            ['testNodeOrCollectionOneTrue.json'],
            ['testNodeNotCollectionTrue.json'],
            ['testNodeAtLeastCollectionTrue.json'],
            ['testNodeAtLeastCollectionFalse.json'],
            ['testNodeExactlyCollectionTrue.json'],
            ['testCombined1.json'],
            // Switch node fixtures
            ['testNodeSwitchEqualMatch.json'],
            ['testNodeSwitchOperatorMatch.json'],
            ['testNodeSwitchDefaultFallback.json'],
            ['testNodeSwitchNoDefault.json'],
            ['testNodeSwitchEmptyCases.json'],
            ['testNodeSwitchComplexThen.json'],
        ];
    }

    // ─── File export ──────────────────────────────────────────────────────

    public function testJsonExportToFile(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testNodeValue.json');

        $tmpFile = tempnam(sys_get_temp_dir(), 'thechoice_') . '.json';
        try {
            $this->jsonExporter->exportToFile($node, $tmpFile);

            self::assertFileExists($tmpFile);
            $reparsed = $this->jsonBuilder->parseFile($tmpFile);
            self::assertSame(4, $this->rootProcessor->process($reparsed));
        } finally {
            @unlink($tmpFile);
        }
    }

    public function testYamlExportToFile(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testNodeValue.json');

        $tmpFile = tempnam(sys_get_temp_dir(), 'thechoice_') . '.yaml';
        try {
            $this->yamlExporter->exportToFile($node, $tmpFile);

            self::assertFileExists($tmpFile);
            $reparsed = $this->yamlBuilder->parseFile($tmpFile);
            self::assertSame(4, $this->rootProcessor->process($reparsed));
        } finally {
            @unlink($tmpFile);
        }
    }

    // ─── JSON output structure ────────────────────────────────────────────

    public function testPrettyJsonContainsNewlines(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testNodeValue.json');

        self::assertStringContainsString("\n", $this->jsonExporter->export($node, pretty: true));
    }

    public function testCompactJsonContainsNoNewlines(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testNodeValue.json');

        self::assertStringNotContainsString("\n", $this->jsonExporter->export($node, pretty: false));
    }

    public function testExportedJsonIsValidJson(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testCombined1.json');
        $json = $this->jsonExporter->export($node);

        self::assertNotNull(json_decode($json, true));
        self::assertSame(JSON_ERROR_NONE, json_last_error());
    }

    public function testExportedYamlIsValidYaml(): void
    {
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testCombined1.json');
        $yaml = $this->yamlExporter->export($node);

        self::assertIsString($yaml);
        self::assertNotEmpty($yaml);
    }

    // ─── Root node is always emitted ─────────────────────────────────────

    public function testExportedJsonAlwaysHasRootNode(): void
    {
        // Short-syntax JSON (no explicit root) is auto-wrapped by ArrayBuilder
        $node = $this->jsonBuilder->parseFile($this->testFilesDir . 'Json/testNodeValue.json');

        self::assertInstanceOf(Root::class, $node);

        $decoded = json_decode($this->jsonExporter->export($node), true);
        self::assertSame('root', $decoded['node']);
    }
}
