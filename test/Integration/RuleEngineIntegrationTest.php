<?php

declare(strict_types=1);

namespace TheChoice\Tests\Integration;

use PHPUnit\Framework\TestCase;
use TheChoice\Builder\JsonBuilder;
use TheChoice\Container;
use TheChoice\Engine\RuleEngine;
use TheChoice\Registry\RuleEntry;
use TheChoice\Registry\RuleRegistry;
use TheChoice\Tests\Integration\Contexts\HasVipStatus;
use TheChoice\Tests\Integration\Contexts\VisitCount;
use TheChoice\Tests\Integration\Contexts\WithdrawalCount;

final class RuleEngineIntegrationTest extends TestCase
{
    private Container $container;

    private JsonBuilder $jsonBuilder;

    protected function setUp(): void
    {
        $this->container = new Container([
            'visitCount'      => VisitCount::class,
            'hasVipStatus'    => HasVipStatus::class,
            'withdrawalCount' => WithdrawalCount::class,
        ]);

        /** @var JsonBuilder $builder */
        $builder = $this->container->get(JsonBuilder::class);
        $this->jsonBuilder = $builder;
    }

    public function testRunWithMultipleRulesReturnsReport(): void
    {
        $engine = new RuleEngine($this->container);

        $ruleTrue = $this->jsonBuilder->parse('{"node":"context","context":"withdrawalCount","operator":"equal","value":0}');
        $ruleFalse = $this->jsonBuilder->parse('{"node":"context","context":"withdrawalCount","operator":"equal","value":99}');
        $ruleValue = $this->jsonBuilder->parse('{"node":"value","value":42}');

        $engine->addRule('should_fire', $ruleTrue, priority: 10);
        $engine->addRule('should_skip', $ruleFalse, priority: 5);
        $engine->addRule('static_value', $ruleValue, priority: 1);

        $report = $engine->run();

        self::assertCount(3, $report);

        self::assertTrue($report->hasFired('should_fire'));
        self::assertFalse($report->hasFired('should_skip'));
        self::assertTrue($report->hasFired('static_value'));

        self::assertSame(42, $report->getResult('static_value')->result);

        self::assertCount(2, $report->getFired());
        self::assertCount(1, $report->getSkipped());
    }

    public function testRunExecutesRulesInPriorityOrder(): void
    {
        $engine = new RuleEngine($this->container);

        $engine->addRule('low', $this->jsonBuilder->parse('{"node":"value","value":"low"}'), priority: 1);
        $engine->addRule('high', $this->jsonBuilder->parse('{"node":"value","value":"high"}'), priority: 100);
        $engine->addRule('mid', $this->jsonBuilder->parse('{"node":"value","value":"mid"}'), priority: 50);

        $report = $engine->run();
        $names = array_keys($report->getAll());

        self::assertSame(['high', 'mid', 'low'], $names);
    }

    public function testLoadFromRegistry(): void
    {
        $registry = new RuleRegistry();

        $registry->register(
            name: 'discount_vip',
            node: $this->jsonBuilder->parse('{"node":"value","value":10}'),
            tags: ['discount'],
            priority: 10,
            description: 'VIP discount',
        );
        $registry->register(
            name: 'discount_loyal',
            node: $this->jsonBuilder->parse('{"node":"value","value":5}'),
            tags: ['discount'],
            priority: 5,
        );

        $engine = new RuleEngine($this->container);
        $engine->loadFromRegistry($registry);

        $report = $engine->run();

        self::assertCount(2, $report);
        self::assertTrue($report->hasFired('discount_vip'));
        self::assertSame(10, $report->getResult('discount_vip')->result);
        self::assertSame(5, $report->getResult('discount_loyal')->result);

        $names = array_keys($report->getAll());
        self::assertSame(['discount_vip', 'discount_loyal'], $names);
    }

    public function testAddEntryWorksDirectly(): void
    {
        $engine = new RuleEngine($this->container);

        $entry = new RuleEntry(
            name: 'direct_entry',
            node: $this->jsonBuilder->parse('{"node":"value","value":"hello"}'),
            tags: ['test'],
            priority: 1,
        );

        $engine->addEntry($entry);
        $report = $engine->run();

        self::assertCount(1, $report);
        self::assertSame('hello', $report->getResult('direct_entry')->result);
    }

    public function testClearRemovesAllRules(): void
    {
        $engine = new RuleEngine($this->container);
        $engine->addRule('rule1', $this->jsonBuilder->parse('{"node":"value","value":1}'));

        $engine->clear();

        $report = $engine->run();

        self::assertCount(0, $report);
    }

    public function testEmptyEngineRunReturnsEmptyReport(): void
    {
        $engine = new RuleEngine($this->container);
        $report = $engine->run();

        self::assertCount(0, $report);
        self::assertSame([], $report->getFired());
        self::assertSame([], $report->getSkipped());
    }
}
