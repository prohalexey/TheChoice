<?php

declare(strict_types=1);

namespace TheChoice\Tests\Integration;

use PHPUnit\Framework\TestCase;
use TheChoice\Builder\JsonBuilder;
use TheChoice\Builder\YamlBuilder;
use TheChoice\Container;
use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Exception\InvalidContextCalculation;
use TheChoice\Exception\LogicException;
use TheChoice\Processor\RootProcessor;
use TheChoice\Tests\Integration\Contexts\DepositCount;
use TheChoice\Tests\Integration\Contexts\WithdrawalCount;

final class NegativeScenariosTest extends TestCase
{
    private JsonBuilder $jsonBuilder;

    private YamlBuilder $yamlBuilder;

    private RootProcessor $rootProcessor;

    protected function setUp(): void
    {
        $container = new Container([
            'withdrawalCount' => WithdrawalCount::class,
            'depositCount'    => DepositCount::class,
        ]);

        $this->jsonBuilder = $container->get(JsonBuilder::class);
        $this->yamlBuilder = $container->get(YamlBuilder::class);
        $this->rootProcessor = $container->get(RootProcessor::class);
    }

    // ─── JSON parsing errors ─────────────────────────────────────────────

    public function testParseInvalidJsonThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->jsonBuilder->parse('{invalid json}');
    }

    public function testParseJsonThatIsNotAnObjectThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('JSON must decode to an array');

        $this->jsonBuilder->parse('"just a string"');
    }

    public function testParseJsonFileNotFoundThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not found');

        $this->jsonBuilder->parseFile('/nonexistent/path/rules.json');
    }

    public function testParseJsonWithZeroMaxDepthThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Max depth must be at least 1');

        $this->jsonBuilder->parse('{}', 0);
    }

    // ─── YAML parsing errors ─────────────────────────────────────────────

    public function testParseYamlFileNotFoundThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not found');

        $this->yamlBuilder->parseFile('/nonexistent/path/rules.yaml');
    }

    public function testParseYamlThatIsNotAnArrayThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('YAML must parse to an array');

        $this->yamlBuilder->parse('just a scalar string');
    }

    // ─── Missing required "node" key ─────────────────────────────────────

    public function testMissingNodeKeyInJsonThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"node" property is absent');

        $this->jsonBuilder->parse('{"value": 42}');
    }

    public function testMissingNodeKeyInYamlThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"node" property is absent');

        $this->yamlBuilder->parse("value: 42\n");
    }

    // ─── Unknown node type ───────────────────────────────────────────────

    public function testUnknownNodeTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->jsonBuilder->parse('{"node": "unknownType"}');
    }

    // ─── Unknown operator ────────────────────────────────────────────────

    public function testUnknownOperatorThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $json = json_encode([
            'node'     => 'context',
            'context'  => 'withdrawalCount',
            'operator' => 'nonExistentOperator',
            'value'    => 0,
        ]);
        $this->jsonBuilder->parse($json);
    }

    // ─── Missing required fields in node types ───────────────────────────

    public function testConditionNodeMissingIfFieldThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('"if" property is absent');

        $json = json_encode([
            'node' => 'condition',
            'then' => ['node' => 'value', 'value' => 1],
        ]);
        $this->jsonBuilder->parse($json);
    }

    public function testConditionNodeMissingThenFieldThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('"then" property is absent');

        $json = json_encode([
            'node' => 'condition',
            'if'   => ['node' => 'value', 'value' => true],
        ]);
        $this->jsonBuilder->parse($json);
    }

    public function testValueNodeMissingValueFieldThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('"value" property is absent');

        $json = json_encode(['node' => 'value']);
        $this->jsonBuilder->parse($json);
    }

    public function testRootNodeMissingRulesFieldThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('"rules" property is absent');

        $json = json_encode(['node' => 'root']);
        $this->jsonBuilder->parse($json);
    }

    // ─── Collection errors ───────────────────────────────────────────────

    public function testCollectionWithInvalidTypeThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('"xor" given');

        $json = json_encode([
            'node'  => 'collection',
            'type'  => 'xor',
            'nodes' => [],
        ]);
        $this->jsonBuilder->parse($json);
    }

    // ─── Root node validation ─────────────────────────────────────────────

    public function testRootNodeAsNonRootThrowsLogicException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('"Root" cannot be not root node');

        $json = json_encode([
            'node' => 'condition',
            'if'   => ['node' => 'root', 'rules' => ['node' => 'value', 'value' => 0]],
            'then' => ['node' => 'value', 'value' => 1],
        ]);
        $this->jsonBuilder->parse($json);
    }

    // ─── Modifier errors ─────────────────────────────────────────────────

    public function testInvalidModifierExpressionThrowsInvalidContextCalculation(): void
    {
        $this->expectException(InvalidContextCalculation::class);

        $json = json_encode([
            'node'      => 'context',
            'context'   => 'depositCount',
            'modifiers' => ['1 / 0'],  // division by zero — StringCalc throws
        ]);

        $node = $this->jsonBuilder->parse($json);
        $this->rootProcessor->process($node);
    }

    public function testModifierMustBeStringThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('modifier must be string');

        $json = json_encode([
            'node'      => 'context',
            'context'   => 'depositCount',
            'modifiers' => [123],  // integer, not string
        ]);
        $this->jsonBuilder->parse($json);
    }

    // ─── Context not found ────────────────────────────────────────────────

    public function testUnknownContextNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not found');

        $json = json_encode([
            'node'    => 'context',
            'context' => 'nonExistentContext',
        ]);
        $node = $this->jsonBuilder->parse($json);
        $this->rootProcessor->process($node);
    }

    // ─── Builder reuse ─────────────────────────────────────────────────

    public function testBuilderCanBeReusedBetweenParseCalls(): void
    {
        $node1 = $this->jsonBuilder->parse('{"node": "value", "value": 1}');
        self::assertSame(1, $this->rootProcessor->process($node1));

        $node2 = $this->jsonBuilder->parse('{"node": "value", "value": 2}');
        self::assertSame(2, $this->rootProcessor->process($node2));
    }

    // ─── SwitchNode parsing errors ────────────────────────────────────────

    public function testSwitchNodeMissingContextFieldThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('"context" property is absent');

        $json = json_encode([
            'node'  => 'switch',
            'cases' => [['value' => 0, 'then' => ['node' => 'value', 'value' => 1]]],
        ]);
        $this->jsonBuilder->parse($json);
    }

    public function testSwitchNodeMissingCasesFieldThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('"cases" property is absent');

        $json = json_encode([
            'node'    => 'switch',
            'context' => 'withdrawalCount',
        ]);
        $this->jsonBuilder->parse($json);
    }

    public function testSwitchNodeCaseMissingThenFieldThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('"then" property is absent in switch case at index 0');

        $json = json_encode([
            'node'    => 'switch',
            'context' => 'withdrawalCount',
            'cases'   => [['value' => 0]],
        ]);
        $this->jsonBuilder->parse($json);
    }

    public function testSwitchNodeUnknownContextAtRuntimeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not found');

        $json = json_encode([
            'node'    => 'switch',
            'context' => 'nonExistentContext',
            'cases'   => [['value' => 0, 'then' => ['node' => 'value', 'value' => 1]]],
        ]);
        $node = $this->jsonBuilder->parse($json);
        $this->rootProcessor->process($node);
    }
}
