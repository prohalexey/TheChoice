<?php

namespace TheChoice\Tests\Integration;

use PHPUnit\Framework\TestCase;
use TheChoice\Context\ContextInterface;
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

final class OperatorsTest extends TestCase
{
    public function testEqualTest(): void
    {
        self::assertTrue((new Equal())->setValue(1)->assert($this->getContext(1)));
        self::assertFalse((new Equal())->setValue('1')->assert($this->getContext(1)));
        self::assertFalse((new Equal())->setValue(2)->assert($this->getContext(1)));

        self::assertTrue((new Equal())->setValue('1')->assert($this->getContext('1')));
        self::assertFalse((new Equal())->setValue(1)->assert($this->getContext('1')));
        self::assertFalse((new Equal())->setValue('2')->assert($this->getContext(1)));

        self::assertTrue((new Equal())->setValue([1])->assert($this->getContext([1])));
        self::assertFalse((new Equal())->setValue([1])->assert($this->getContext([2])));
    }

    public function testNotEqualTest(): void
    {
        self::assertFalse((new NotEqual())->setValue(1)->assert($this->getContext(1)));
        self::assertTrue((new NotEqual())->setValue('1')->assert($this->getContext(1)));
        self::assertTrue((new NotEqual())->setValue(2)->assert($this->getContext(1)));

        self::assertFalse((new NotEqual())->setValue('1')->assert($this->getContext('1')));
        self::assertTrue((new NotEqual())->setValue(1)->assert($this->getContext('1')));
        self::assertTrue((new NotEqual())->setValue('2')->assert($this->getContext(1)));

        self::assertFalse((new NotEqual())->setValue([1])->assert($this->getContext([1])));
        self::assertTrue((new NotEqual())->setValue([1])->assert($this->getContext([2])));
    }

    public function testGreaterThanTest(): void
    {
        self::assertTrue((new GreaterThan())->setValue(1)->assert($this->getContext(2)));
        self::assertFalse((new GreaterThan())->setValue(2)->assert($this->getContext(1)));
    }

    public function testGreaterThanOrEqualTest(): void
    {
        self::assertTrue((new GreaterThanOrEqual())->setValue(1)->assert($this->getContext(2)));
        self::assertTrue((new GreaterThanOrEqual())->setValue(1)->assert($this->getContext(1)));
        self::assertFalse((new GreaterThanOrEqual())->setValue(2)->assert($this->getContext(1)));
    }

    public function testLowerThanTest(): void
    {
        self::assertFalse((new LowerThan())->setValue(1)->assert($this->getContext(2)));
        self::assertTrue((new LowerThan())->setValue(2)->assert($this->getContext(1)));
    }

    public function testLowerThanOrEqualTest(): void
    {
        self::assertFalse((new LowerThanOrEqual())->setValue(1)->assert($this->getContext(2)));
        self::assertTrue((new LowerThanOrEqual())->setValue(1)->assert($this->getContext(1)));
        self::assertTrue((new LowerThanOrEqual())->setValue(2)->assert($this->getContext(1)));
    }

    public function testStringContainTest(): void
    {
        $operator = (new StringContain())->setValue('test');

        self::assertTrue($operator->assert($this->getContext('test')));
        self::assertTrue($operator->assert($this->getContext('atest')));
        self::assertTrue($operator->assert($this->getContext('testa')));
        self::assertFalse($operator->assert($this->getContext('aa')));
    }

    public function testStringNotContainTest(): void
    {
        $operator = (new StringNotContain())->setValue('test');

        self::assertFalse($operator->assert($this->getContext('test')));
        self::assertFalse($operator->assert($this->getContext('atest')));
        self::assertFalse($operator->assert($this->getContext('testa')));
        self::assertTrue($operator->assert($this->getContext('aa')));
    }

    public function testArrayContainTest(): void
    {
        $operator = (new ArrayContain())->setValue([1, 2, 3, 'a']);

        self::assertTrue($operator->assert($this->getContext(1)));
        self::assertTrue($operator->assert($this->getContext(2)));
        self::assertTrue($operator->assert($this->getContext(3)));
        self::assertTrue($operator->assert($this->getContext('a')));
        self::assertFalse($operator->assert($this->getContext('b')));
        self::assertFalse($operator->assert($this->getContext(4)));
    }

    public function testArrayNotContainTest(): void
    {
        $operator = (new ArrayNotContain())->setValue([1, 2, 3, 'a']);

        self::assertFalse($operator->assert($this->getContext(1)));
        self::assertFalse($operator->assert($this->getContext(2)));
        self::assertFalse($operator->assert($this->getContext(3)));
        self::assertFalse($operator->assert($this->getContext('a')));
        self::assertTrue($operator->assert($this->getContext('b')));
        self::assertTrue($operator->assert($this->getContext(4)));
    }

    public function testArrayNumericInRangeTest(): void
    {
        $operator = (new NumericInRange())->setValue([1, 5]);

        self::assertTrue($operator->assert($this->getContext(1)));
        self::assertTrue($operator->assert($this->getContext(3)));
        self::assertTrue($operator->assert($this->getContext(5)));
        self::assertFalse($operator->assert($this->getContext(0)));
        self::assertFalse($operator->assert($this->getContext(6)));
        self::assertTrue($operator->assert($this->getContext(1)));
        self::assertTrue($operator->assert($this->getContext(3)));
        self::assertTrue($operator->assert($this->getContext(5)));
        self::assertFalse($operator->assert($this->getContext(0)));
        self::assertFalse($operator->assert($this->getContext(6)));
    }

    private function getContext(int|string|array $value): ContextInterface
    {
        return new class($value) implements ContextInterface {
            private $value;

            public function __construct($value)
            {
                $this->value = $value;
            }

            public function getValue(): mixed
            {
                return $this->value;
            }
        };
    }
}
