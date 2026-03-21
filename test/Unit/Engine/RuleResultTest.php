<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Engine;

use PHPUnit\Framework\TestCase;
use TheChoice\Engine\RuleResult;

final class RuleResultTest extends TestCase
{
    public function testFiredIsTrueForNonNullNonFalseResult(): void
    {
        self::assertTrue((new RuleResult('r', 42))->fired);
        self::assertTrue((new RuleResult('r', 'text'))->fired);
        self::assertTrue((new RuleResult('r', 0))->fired);
        self::assertTrue((new RuleResult('r', ''))->fired);
        self::assertTrue((new RuleResult('r', true))->fired);
        self::assertTrue((new RuleResult('r', []))->fired);
    }

    public function testFiredIsFalseForNullOrFalse(): void
    {
        self::assertFalse((new RuleResult('r', null))->fired);
        self::assertFalse((new RuleResult('r', false))->fired);
    }

    public function testPropertiesAreAccessible(): void
    {
        $result = new RuleResult(name: 'my_rule', result: 99);

        self::assertSame('my_rule', $result->name);
        self::assertSame(99, $result->result);
    }
}
