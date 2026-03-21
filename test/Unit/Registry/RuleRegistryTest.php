<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Registry;

use PHPUnit\Framework\TestCase;
use TheChoice\Exception\DuplicateRuleException;
use TheChoice\Exception\RuleNotFoundException;
use TheChoice\Node\Value;
use TheChoice\Registry\RuleRegistry;

final class RuleRegistryTest extends TestCase
{
    private RuleRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new RuleRegistry();
    }

    public function testRegisterAndGet(): void
    {
        $node = new Value(42);

        $this->registry->register(name: 'my_rule', node: $node, priority: 5);
        $entry = $this->registry->get('my_rule');

        self::assertSame('my_rule', $entry->name);
        self::assertSame($node, $entry->node);
        self::assertSame(5, $entry->priority);
    }

    public function testHasReturnsTrueForRegistered(): void
    {
        $this->registry->register(name: 'exists', node: new Value(1));

        self::assertTrue($this->registry->has('exists'));
        self::assertFalse($this->registry->has('missing'));
    }

    public function testGetThrowsForMissing(): void
    {
        $this->expectException(RuleNotFoundException::class);
        $this->expectExceptionMessage('"missing"');

        $this->registry->get('missing');
    }

    public function testRegisterThrowsOnDuplicate(): void
    {
        $this->registry->register(name: 'dup', node: new Value(1));

        $this->expectException(DuplicateRuleException::class);
        $this->expectExceptionMessage('"dup"');

        $this->registry->register(name: 'dup', node: new Value(2));
    }

    public function testRemove(): void
    {
        $this->registry->register(name: 'to_remove', node: new Value(1));
        self::assertTrue($this->registry->has('to_remove'));

        $this->registry->remove('to_remove');
        self::assertFalse($this->registry->has('to_remove'));
    }

    public function testRemoveThrowsForMissing(): void
    {
        $this->expectException(RuleNotFoundException::class);

        $this->registry->remove('nonexistent');
    }

    public function testFindByTag(): void
    {
        $this->registry->register(name: 'rule_a', node: new Value(1), tags: ['discount', 'vip']);
        $this->registry->register(name: 'rule_b', node: new Value(2), tags: ['discount']);
        $this->registry->register(name: 'rule_c', node: new Value(3), tags: ['security']);

        $discountRules = $this->registry->findByTag('discount');
        self::assertCount(2, $discountRules);

        $names = array_map(static fn ($entry): string => $entry->name, $discountRules);
        self::assertContains('rule_a', $names);
        self::assertContains('rule_b', $names);

        self::assertCount(1, $this->registry->findByTag('vip'));
        self::assertCount(0, $this->registry->findByTag('nonexistent'));
    }

    public function testAllReturnsSortedByPriorityDescending(): void
    {
        $this->registry->register(name: 'low', node: new Value(1), priority: 1);
        $this->registry->register(name: 'high', node: new Value(2), priority: 100);
        $this->registry->register(name: 'mid', node: new Value(3), priority: 50);

        $all = $this->registry->all();
        self::assertCount(3, $all);
        self::assertSame('high', $all[0]->name);
        self::assertSame('mid', $all[1]->name);
        self::assertSame('low', $all[2]->name);
    }

    public function testCountReturnsNumberOfEntries(): void
    {
        self::assertCount(0, $this->registry);

        $this->registry->register(name: 'a', node: new Value(1));
        $this->registry->register(name: 'b', node: new Value(2));

        self::assertCount(2, $this->registry);
    }
}
