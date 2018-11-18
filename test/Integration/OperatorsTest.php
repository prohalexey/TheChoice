<?php

namespace TheChoice\Tests\Integration;

use \PHPUnit\Framework\TestCase;

use TheChoice\{
    Contracts\RuleContextInterface, Operators\Equal, Operators\NotEqual, Operators\GreaterThan, Operators\GreaterThanOrEqual, Operators\LowerThan, Operators\LowerThanOrEqual, Operators\NumericInRange, Operators\StringContain, Operators\StringNotContain, Operators\ArrayContain, Operators\ArrayNotContain
};

final class OperatorsTest extends TestCase
{
    /**
     * @test
     */
    public function EqualTest()
    {
        self::assertTrue((new Equal(1))->assert($this->getContext(1)));
        self::assertFalse((new Equal('1'))->assert($this->getContext(1)));
        self::assertFalse((new Equal(2))->assert($this->getContext(1)));

        self::assertTrue((new Equal('1'))->assert($this->getContext('1')));
        self::assertFalse((new Equal(1))->assert($this->getContext('1')));
        self::assertFalse((new Equal('2'))->assert($this->getContext(1)));

        self::assertTrue((new Equal([1]))->assert($this->getContext([1])));
        self::assertFalse((new Equal([1]))->assert($this->getContext([2])));
    }

    /**
     * @test
     */
    public function NotEqualTest()
    {
        self::assertFalse((new NotEqual(1))->assert($this->getContext(1)));
        self::assertTrue((new NotEqual('1'))->assert($this->getContext(1)));
        self::assertTrue((new NotEqual(2))->assert($this->getContext(1)));

        self::assertFalse((new NotEqual('1'))->assert($this->getContext('1')));
        self::assertTrue((new NotEqual(1))->assert($this->getContext('1')));
        self::assertTrue((new NotEqual('2'))->assert($this->getContext(1)));

        self::assertFalse((new NotEqual([1]))->assert($this->getContext([1])));
        self::assertTrue((new NotEqual([1]))->assert($this->getContext([2])));
    }

    /**
     * @test
     */
    public function GreaterThanTest()
    {
        self::assertTrue((new GreaterThan(1))->assert($this->getContext(2)));
        self::assertFalse((new GreaterThan(2))->assert($this->getContext(1)));
    }

    /**
     * @test
     */
    public function GreaterThanOrEqualTest()
    {
        self::assertTrue((new GreaterThanOrEqual(1))->assert($this->getContext(2)));
        self::assertTrue((new GreaterThanOrEqual(1))->assert($this->getContext(1)));
        self::assertFalse((new GreaterThanOrEqual(2))->assert($this->getContext(1)));
    }

    /**
     * @test
     */
    public function LowerThanTest()
    {
        self::assertFalse((new LowerThan(1))->assert($this->getContext(2)));
        self::assertTrue((new LowerThan(2))->assert($this->getContext(1)));
    }

    /**
     * @test
     */
    public function LowerThanOrEqualTest()
    {
        self::assertFalse((new LowerThanOrEqual(1))->assert($this->getContext(2)));
        self::assertTrue((new LowerThanOrEqual(1))->assert($this->getContext(1)));
        self::assertTrue((new LowerThanOrEqual(2))->assert($this->getContext(1)));
    }

    /**
     * @test
     */
    public function StringContainTest()
    {
        $operator = new StringContain('test');

        self::assertTrue($operator->assert($this->getContext('test')));
        self::assertTrue($operator->assert($this->getContext('atest')));
        self::assertTrue($operator->assert($this->getContext('testa')));
        self::assertFalse($operator->assert($this->getContext('aa')));
    }

    /**
     * @test
     */
    public function StringNotContainTest()
    {
        $operator = new StringNotContain('test');

        self::assertFalse($operator->assert($this->getContext('test')));
        self::assertFalse($operator->assert($this->getContext('atest')));
        self::assertFalse($operator->assert($this->getContext('testa')));
        self::assertTrue($operator->assert($this->getContext('aa')));
    }

    /**
     * @test
     */
    public function ArrayContainTest()
    {
        $operator = new ArrayContain([1, 2, 3, 'a']);

        self::assertTrue($operator->assert($this->getContext(1)));
        self::assertTrue($operator->assert($this->getContext(2)));
        self::assertTrue($operator->assert($this->getContext(3)));
        self::assertTrue($operator->assert($this->getContext('a')));
        self::assertFalse($operator->assert($this->getContext('b')));
        self::assertFalse($operator->assert($this->getContext(4)));
    }

    /**
     * @test
     */
    public function ArrayNotContainTest()
    {
        $operator = new ArrayNotContain([1, 2, 3, 'a']);

        self::assertFalse($operator->assert($this->getContext(1)));
        self::assertFalse($operator->assert($this->getContext(2)));
        self::assertFalse($operator->assert($this->getContext(3)));
        self::assertFalse($operator->assert($this->getContext('a')));
        self::assertTrue($operator->assert($this->getContext('b')));
        self::assertTrue($operator->assert($this->getContext(4)));
    }

    /**
     * @test
     */
    public function ArrayNumericInRangeTest()
    {
        $operator = new NumericInRange([1 ,5]);

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

    private function getContext($value)
    {
        return new class($value) implements RuleContextInterface
        {
            private $value;

            public function __construct($value)
            {
                $this->value = $value;
            }

            public function getValue()
            {
                return $this->value;
            }
        };
    }
}