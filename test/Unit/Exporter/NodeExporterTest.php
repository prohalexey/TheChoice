<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Exporter;

use PHPUnit\Framework\TestCase;
use TheChoice\Builder\RuleBuilder;
use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Exporter\JsonNodeExporter;
use TheChoice\Exporter\NodeSerializer;
use TheChoice\Exporter\YamlNodeExporter;
use TheChoice\Node\AbstractChildNode;
use TheChoice\Node\Root;
use TheChoice\Node\Value;

final class NodeExporterTest extends TestCase
{
    private NodeSerializer $serializer;

    private JsonNodeExporter $jsonExporter;

    private YamlNodeExporter $yamlExporter;

    protected function setUp(): void
    {
        $this->serializer = new NodeSerializer();
        $this->jsonExporter = new JsonNodeExporter($this->serializer);
        $this->yamlExporter = new YamlNodeExporter($this->serializer);
    }

    // ─── JsonNodeExporter ─────────────────────────────────────────────────

    public function testJsonExportProducesValidJson(): void
    {
        $node = RuleBuilder::root()->rules(RuleBuilder::value(42))->build();

        $json = $this->jsonExporter->export($node);

        $decoded = json_decode($json, true);
        self::assertIsArray($decoded);
        self::assertSame('root', $decoded['node']);
    }

    public function testJsonExportPrettyPrintIsEnabledByDefault(): void
    {
        $node = RuleBuilder::root()->rules(RuleBuilder::value(1))->build();

        $json = $this->jsonExporter->export($node);

        // Pretty-printed JSON contains newlines
        self::assertStringContainsString("\n", $json);
    }

    public function testJsonExportCompactProducesNoNewlines(): void
    {
        $node = RuleBuilder::root()->rules(RuleBuilder::value(1))->build();

        $json = $this->jsonExporter->export($node, pretty: false);

        self::assertStringNotContainsString("\n", $json);
    }

    public function testJsonExportCompactIsValidJson(): void
    {
        $node = RuleBuilder::root()
            ->rules(
                RuleBuilder::condition()
                    ->if(RuleBuilder::context('ctx')->equal(1))
                    ->then(RuleBuilder::value('yes'))
                    ->else(RuleBuilder::value('no')),
            )
            ->build()
        ;

        $json = $this->jsonExporter->export($node, pretty: false);

        $decoded = json_decode($json, true);
        self::assertIsArray($decoded);
    }

    public function testJsonExportToFileWritesFile(): void
    {
        $node = new Value(99);
        $tmpFile = sys_get_temp_dir() . '/the-choice-test-' . uniqid() . '.json';

        try {
            $this->jsonExporter->exportToFile($node, $tmpFile);

            self::assertFileExists($tmpFile);
            $decoded = json_decode(file_get_contents($tmpFile), true);
            self::assertSame(99, $decoded['value']);
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }

    public function testJsonExportToFileThrowsWhenPathNotWritable(): void
    {
        $node = new Value(1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to write JSON');

        @$this->jsonExporter->exportToFile($node, '/nonexistent/directory/file.json');
    }

    public function testJsonExportPreservesUnicodeCharacters(): void
    {
        $node = new Value('Привет мир');

        $json = $this->jsonExporter->export($node);

        self::assertStringContainsString('Привет мир', $json);
    }

    public function testJsonExportPreservesSlashesUnescaped(): void
    {
        $node = new Value('path/to/resource');

        $json = $this->jsonExporter->export($node);

        // JSON_UNESCAPED_SLASHES: slashes should not be escaped
        self::assertStringContainsString('path/to/resource', $json);
    }

    // ─── YamlNodeExporter ─────────────────────────────────────────────────

    public function testYamlExportProducesValidYaml(): void
    {
        $node = RuleBuilder::root()->rules(RuleBuilder::value(42))->build();

        $yaml = $this->yamlExporter->export($node);

        self::assertStringContainsString('node: root', $yaml);
        self::assertStringContainsString('value: 42', $yaml);
    }

    public function testYamlExportToFileWritesFile(): void
    {
        $node = new Value(99);
        $tmpFile = sys_get_temp_dir() . '/the-choice-test-' . uniqid() . '.yaml';

        try {
            $this->yamlExporter->exportToFile($node, $tmpFile);

            self::assertFileExists($tmpFile);
            self::assertStringContainsString('99', (string)file_get_contents($tmpFile));
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }

    public function testYamlExportToFileThrowsWhenPathNotWritable(): void
    {
        $node = new Value(1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to write YAML');

        @$this->yamlExporter->exportToFile($node, '/nonexistent/directory/file.yaml');
    }

    public function testYamlExportCustomInlineAndIndent(): void
    {
        $node = RuleBuilder::root()->rules(RuleBuilder::value(1))->build();

        // Should not throw with custom parameters
        $yaml = $this->yamlExporter->export($node, inline: 6, indent: 4);

        self::assertIsString($yaml);
        self::assertNotEmpty($yaml);
    }

    // ─── NodeSerializer — unsupported node type ───────────────────────────

    public function testSerializerThrowsForUnsupportedNodeType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported node type');

        // Custom node that NodeSerializer doesn't know about
        $customNode = new class extends AbstractChildNode {
            public static function getNodeName(): string
            {
                return 'unknown';
            }
        };

        $this->serializer->toArray($customNode);
    }

    // ─── NodeSerializer — Collection description ──────────────────────────

    public function testSerializesCollectionDescription(): void
    {
        $node = RuleBuilder::collection('and')
            ->description('my collection')
            ->add(RuleBuilder::value(1))
            ->build()
        ;

        $array = $this->serializer->toArray($node);

        self::assertSame('my collection', $array['description']);
    }

    // ─── Round-trip: fluent → export → parse check ───────────────────────

    public function testJsonRoundtripPreservesStructure(): void
    {
        $original = RuleBuilder::root()
            ->rules(
                RuleBuilder::condition()
                    ->if(RuleBuilder::context('depositCount')->greaterThan(0))
                    ->then(RuleBuilder::value(true))
                    ->else(RuleBuilder::value(false)),
            )
            ->build()
        ;

        $json = $this->jsonExporter->export($original);

        $decoded = json_decode($json, true);
        self::assertSame('root', $decoded['node']);
        self::assertSame('condition', $decoded['rules']['node']);
        self::assertSame('context', $decoded['rules']['if']['node']);
        self::assertSame('greaterThan', $decoded['rules']['if']['operator']);
    }

    // ─── Root node description serialization ─────────────────────────────

    public function testRootWithDescriptionIsSerializedCorrectly(): void
    {
        $root = RuleBuilder::root()
            ->rules(RuleBuilder::value(1))
            ->description('my rule')
            ->build()
        ;

        $array = $this->serializer->toArray($root);

        self::assertSame('my rule', $array['description']);
    }
}
