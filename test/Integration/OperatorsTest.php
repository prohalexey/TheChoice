<?php

use \PHPUnit\Framework\TestCase;

use TheChoice\ {
    Contracts\ContextInterface,

    Operators\Equal,
    Operators\NotEqual,
    Operators\GreaterThan,
    Operators\GreaterThanOrEqual,
    Operators\LowerThan,
    Operators\LowerThanOrEqual,
    Operators\StringContain,
    Operators\StringNotContain,
    Operators\ArrayContain,
    Operators\ArrayNotContain
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
        self::assertTrue((new StringContain('test'))->assert($this->getContext('test')));
        self::assertTrue((new StringContain('test'))->assert($this->getContext('atest')));
        self::assertTrue((new StringContain('test'))->assert($this->getContext('testa')));
        self::assertFalse((new StringContain('test'))->assert($this->getContext('aa')));
    }

    /**
     * @test
     */
    public function StringNotContainTest()
    {
        self::assertFalse((new StringNotContain('test'))->assert($this->getContext('test')));
        self::assertFalse((new StringNotContain('test'))->assert($this->getContext('atest')));
        self::assertFalse((new StringNotContain('test'))->assert($this->getContext('testa')));
        self::assertTrue((new StringNotContain('test'))->assert($this->getContext('aa')));
    }

    /**
     * @test
     */
    public function ArrayContainTest()
    {
        self::assertTrue((new ArrayContain([1, 2, 3, 'a']))->assert($this->getContext(1)));
        self::assertTrue((new ArrayContain([1, 2, 3, 'a']))->assert($this->getContext(2)));
        self::assertTrue((new ArrayContain([1, 2, 3, 'a']))->assert($this->getContext(3)));
        self::assertTrue((new ArrayContain([1, 2, 3, 'a']))->assert($this->getContext('a')));
        self::assertFalse((new ArrayContain([1, 2, 3, 'a']))->assert($this->getContext('b')));
        self::assertFalse((new ArrayContain([1, 2, 3, 'a']))->assert($this->getContext(4)));
    }

    /**
     * @test
     */
    public function ArrayNotContainTest()
    {
        self::assertFalse((new ArrayNotContain([1, 2, 3, 'a']))->assert($this->getContext(1)));
        self::assertFalse((new ArrayNotContain([1, 2, 3, 'a']))->assert($this->getContext(2)));
        self::assertFalse((new ArrayNotContain([1, 2, 3, 'a']))->assert($this->getContext(3)));
        self::assertFalse((new ArrayNotContain([1, 2, 3, 'a']))->assert($this->getContext('a')));
        self::assertTrue((new ArrayNotContain([1, 2, 3, 'a']))->assert($this->getContext('b')));
        self::assertTrue((new ArrayNotContain([1, 2, 3, 'a']))->assert($this->getContext(4)));
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