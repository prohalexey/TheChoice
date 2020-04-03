<?php

namespace TheChoice\Tests\Integration;

use \PHPUnit\Framework\TestCase;

use TheChoice\{
    Context\ContextInterface,

    Operator\Equal,
    Operator\NotEqual,
    Operator\GreaterThan,
    Operator\GreaterThanOrEqual,
    Operator\LowerThan,
    Operator\LowerThanOrEqual,
    Operator\NumericInRange,
    Operator\StringContain,
    Operator\StringNotContain,
    Operator\ArrayContain,
    Operator\ArrayNotContain
};

final class OperatorsTest extends TestCase
{
    /**
     * @test
     */
    public function EqualTest()
    {
        self::assertTrue(((new Equal())->setValue(1))->assert($this->getContext(1)));
        self::assertFalse(((new Equal())->setValue('1'))->assert($this->getContext(1)));
        self::assertFalse(((new Equal())->setValue(2))->assert($this->getContext(1)));

        self::assertTrue(((new Equal())->setValue('1'))->assert($this->getContext('1')));
        self::assertFalse(((new Equal())->setValue(1))->assert($this->getContext('1')));
        self::assertFalse(((new Equal())->setValue('2'))->assert($this->getContext(1)));

        self::assertTrue(((new Equal())->setValue([1]))->assert($this->getContext([1])));
        self::assertFalse(((new Equal())->setValue([1]))->assert($this->getContext([2])));
    }

    /**
     * @test
     */
    public function NotEqualTest()
    {
        self::assertFalse(((new NotEqual())->setValue(1))->assert($this->getContext(1)));
        self::assertTrue(((new NotEqual())->setValue('1'))->assert($this->getContext(1)));
        self::assertTrue(((new NotEqual())->setValue(2))->assert($this->getContext(1)));

        self::assertFalse(((new NotEqual())->setValue('1'))->assert($this->getContext('1')));
        self::assertTrue(((new NotEqual())->setValue(1))->assert($this->getContext('1')));
        self::assertTrue(((new NotEqual())->setValue('2'))->assert($this->getContext(1)));

        self::assertFalse(((new NotEqual())->setValue([1]))->assert($this->getContext([1])));
        self::assertTrue(((new NotEqual())->setValue([1]))->assert($this->getContext([2])));
    }

    /**
     * @test
     */
    public function GreaterThanTest()
    {
        self::assertTrue(((new GreaterThan())->setValue(1))->assert($this->getContext(2)));
        self::assertFalse(((new GreaterThan())->setValue(2))->assert($this->getContext(1)));
    }

    /**
     * @test
     */
    public function GreaterThanOrEqualTest()
    {
        self::assertTrue(((new GreaterThanOrEqual())->setValue(1))->assert($this->getContext(2)));
        self::assertTrue(((new GreaterThanOrEqual())->setValue(1))->assert($this->getContext(1)));
        self::assertFalse(((new GreaterThanOrEqual())->setValue(2))->assert($this->getContext(1)));
    }

    /**
     * @test
     */
    public function LowerThanTest()
    {
        self::assertFalse(((new LowerThan())->setValue(1))->assert($this->getContext(2)));
        self::assertTrue(((new LowerThan())->setValue(2))->assert($this->getContext(1)));
    }

    /**
     * @test
     */
    public function LowerThanOrEqualTest()
    {
        self::assertFalse(((new LowerThanOrEqual())->setValue(1))->assert($this->getContext(2)));
        self::assertTrue(((new LowerThanOrEqual())->setValue(1))->assert($this->getContext(1)));
        self::assertTrue(((new LowerThanOrEqual())->setValue(2))->assert($this->getContext(1)));
    }

    /**
     * @test
     */
    public function StringContainTest()
    {
        $operator = (new StringContain())->setValue('test');

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
        $operator = (new StringNotContain())->setValue('test');

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
        $operator = (new ArrayContain())->setValue([1, 2, 3, 'a']);

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
        $operator = (new ArrayNotContain())->setValue([1, 2, 3, 'a']);

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
        $operator = (new NumericInRange())->setValue([1 ,5]);

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
        return new class($value) implements ContextInterface
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