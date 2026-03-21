<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Operator;

use PHPUnit\Framework\TestCase;
use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Operator\ArrayContain;
use TheChoice\Operator\ArrayNotContain;
use TheChoice\Operator\ContainsKey;
use TheChoice\Operator\CountEqual;
use TheChoice\Operator\CountGreaterThan;
use TheChoice\Operator\EndsWith;
use TheChoice\Operator\Equal;
use TheChoice\Operator\GreaterThan;
use TheChoice\Operator\GreaterThanOrEqual;
use TheChoice\Operator\IsEmpty;
use TheChoice\Operator\IsInstanceOf;
use TheChoice\Operator\IsNull;
use TheChoice\Operator\LowerThan;
use TheChoice\Operator\LowerThanOrEqual;
use TheChoice\Operator\MatchesRegex;
use TheChoice\Operator\NotEqual;
use TheChoice\Operator\NumericInRange;
use TheChoice\Operator\StartsWith;
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

    // ─── StartsWith ──────────────────────────────────────────────────────────

    public function testStartsWithThrowsWhenValueIsNotString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new StartsWith())->setValue(42);
    }

    // ─── EndsWith ────────────────────────────────────────────────────────────

    public function testEndsWithThrowsWhenValueIsNotString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new EndsWith())->setValue(42);
    }

    // ─── MatchesRegex ────────────────────────────────────────────────────────

    public function testMatchesRegexThrowsWhenValueIsNotString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new MatchesRegex())->setValue(42);
    }

    public function testMatchesRegexThrowsWhenPatternIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('valid regex pattern');

        (new MatchesRegex())->setValue('not-a-regex');
    }

    // ─── IsInstanceOf ────────────────────────────────────────────────────────

    public function testIsInstanceOfThrowsWhenValueIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new IsInstanceOf())->setValue('');
    }

    public function testIsInstanceOfThrowsWhenValueIsNotString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new IsInstanceOf())->setValue(42);
    }

    // ─── ContainsKey ─────────────────────────────────────────────────────────

    public function testContainsKeyThrowsWhenValueIsFloat(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ContainsKey())->setValue(3.14);
    }

    // ─── CountEqual ──────────────────────────────────────────────────────────

    public function testCountEqualThrowsWhenValueIsNotNumeric(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new CountEqual())->setValue('abc');
    }

    // ─── CountGreaterThan ────────────────────────────────────────────────────

    public function testCountGreaterThanThrowsWhenValueIsNotNumeric(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new CountGreaterThan())->setValue('abc');
    }

    // ─── Operator name consistency ───────────────────────────────────────────

    public function testAllOperatorNamesAreNonEmpty(): void
    {
        $operators = [
            ArrayContain::class,
            ArrayNotContain::class,
            ContainsKey::class,
            CountEqual::class,
            CountGreaterThan::class,
            EndsWith::class,
            Equal::class,
            GreaterThan::class,
            GreaterThanOrEqual::class,
            IsEmpty::class,
            IsInstanceOf::class,
            IsNull::class,
            LowerThan::class,
            LowerThanOrEqual::class,
            MatchesRegex::class,
            NotEqual::class,
            NumericInRange::class,
            StartsWith::class,
            StringContain::class,
            StringNotContain::class,
        ];

        foreach ($operators as $operatorClass) {
            self::assertNotEmpty($operatorClass::getOperatorName(), "{$operatorClass} must have a non-empty name");
        }
    }
}
