<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Operator;

use PHPUnit\Framework\TestCase;
use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Operator\ArrayContain;
use TheChoice\Operator\ArrayNotContain;
use TheChoice\Operator\Equal;
use TheChoice\Operator\GreaterThan;
use TheChoice\Operator\GreaterThanOrEqual;
use TheChoice\Operator\LowerThan;
use TheChoice\Operator\LowerThanOrEqual;
use TheChoice\Operator\NotEqual;
use TheChoice\Operator\NumericInRange;
use TheChoice\Operator\StringContain;
use TheChoice\Operator\StringNotContain;

final class OperatorValidationTest extends TestCase
{
    // ─── ArrayContain ────────────────────────────────────────────────────────

    public function testArrayContainThrowsWhenValueIsNotArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not an array');

        (new ArrayContain())->setValue('not-an-array');
    }

    public function testArrayContainThrowsWhenValueIsInt(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ArrayContain())->setValue(42);
    }

    // ─── ArrayNotContain ─────────────────────────────────────────────────────

    public function testArrayNotContainThrowsWhenValueIsNotArray(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ArrayNotContain())->setValue('string');
    }

    // ─── NumericInRange ──────────────────────────────────────────────────────

    public function testNumericInRangeThrowsWhenValueIsNotArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not an array');

        (new NumericInRange())->setValue(5);
    }

    public function testNumericInRangeThrowsWhenArrayHasWrongCount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exact 2 args');

        (new NumericInRange())->setValue([1, 2, 3]);
    }

    public function testNumericInRangeThrowsWhenArrayHasOneElement(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new NumericInRange())->setValue([1]);
    }

    // ─── StringContain ───────────────────────────────────────────────────────

    public function testStringContainGetOperatorNameReturnsStringContain(): void
    {
        self::assertSame('stringContain', StringContain::getOperatorName());
    }

    public function testStringNotContainGetOperatorNameReturnsStringNotContain(): void
    {
        self::assertSame('stringNotContain', StringNotContain::getOperatorName());
    }

    // ─── Operator name consistency ───────────────────────────────────────────

    public function testAllOperatorNamesAreNonEmpty(): void
    {
        $operators = [
            ArrayContain::class,
            ArrayNotContain::class,
            Equal::class,
            GreaterThan::class,
            GreaterThanOrEqual::class,
            LowerThan::class,
            LowerThanOrEqual::class,
            NotEqual::class,
            NumericInRange::class,
            StringContain::class,
            StringNotContain::class,
        ];

        foreach ($operators as $operatorClass) {
            self::assertNotEmpty($operatorClass::getOperatorName(), "{$operatorClass} must have a non-empty name");
        }
    }
}
