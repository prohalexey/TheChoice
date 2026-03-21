<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use TheChoice\Engine\EngineReport;
use TheChoice\Engine\RuleResult;
use TheChoice\Exception\RuleNotFoundException;

final class EngineReportTest extends TestCase
{
    private EngineReport $report;

    protected function setUp(): void
    {
        $this->report = new EngineReport([
            'fired_rule'   => new RuleResult('fired_rule', 42),
            'skipped_rule' => new RuleResult('skipped_rule', false),
            'null_rule'    => new RuleResult('null_rule', null),
            'zero_rule'    => new RuleResult('zero_rule', 0),
        ]);
    }

    public function testGetFiredReturnsOnlyFiredRules(): void
    {
        $fired = $this->report->getFired();

        self::assertCount(2, $fired);
        self::assertArrayHasKey('fired_rule', $fired);
        self::assertArrayHasKey('zero_rule', $fired);
    }

    public function testGetSkippedReturnsOnlySkippedRules(): void
    {
        $skipped = $this->report->getSkipped();

        self::assertCount(2, $skipped);
        self::assertArrayHasKey('skipped_rule', $skipped);
        self::assertArrayHasKey('null_rule', $skipped);
    }

    public function testGetAllReturnsEverything(): void
    {
        self::assertCount(4, $this->report->getAll());
    }

    public function testHasFired(): void
    {
        self::assertTrue($this->report->hasFired('fired_rule'));
        self::assertTrue($this->report->hasFired('zero_rule'));
        self::assertFalse($this->report->hasFired('skipped_rule'));
        self::assertFalse($this->report->hasFired('null_rule'));
        self::assertFalse($this->report->hasFired('nonexistent'));
    }

    public function testGetResultReturnsRuleResult(): void
    {
        $result = $this->report->getResult('fired_rule');

        self::assertSame(42, $result->result);
        self::assertTrue($result->fired);
    }

    public function testGetResultThrowsForMissing(): void
    {
        $this->expectException(RuleNotFoundException::class);
        $this->expectExceptionMessage('"unknown"');

        $this->report->getResult('unknown');
    }

    public function testCountReturnsTotal(): void
    {
        self::assertCount(4, $this->report);
    }
}
