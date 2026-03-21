<?php

namespace TheChoice\Tests\Integration;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use TheChoice\Context\ContextInterface;
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

final class OperatorsTest extends TestCase
{
    public function testEqualTest(): void
    {
        self::assertTrue(new Equal()->setValue(1)->assert($this->getContext(1)));
        self::assertFalse(new Equal()->setValue('1')->assert($this->getContext(1)));
        self::assertFalse(new Equal()->setValue(2)->assert($this->getContext(1)));

        self::assertTrue(new Equal()->setValue('1')->assert($this->getContext('1')));
        self::assertFalse(new Equal()->setValue(1)->assert($this->getContext('1')));
        self::assertFalse(new Equal()->setValue('2')->assert($this->getContext(1)));

        self::assertTrue(new Equal()->setValue([1])->assert($this->getContext([1])));
        self::assertFalse(new Equal()->setValue([1])->assert($this->getContext([2])));
    }

    public function testNotEqualTest(): void
    {
        self::assertFalse(new NotEqual()->setValue(1)->assert($this->getContext(1)));
        self::assertTrue(new NotEqual()->setValue('1')->assert($this->getContext(1)));
        self::assertTrue(new NotEqual()->setValue(2)->assert($this->getContext(1)));

        self::assertFalse(new NotEqual()->setValue('1')->assert($this->getContext('1')));
        self::assertTrue(new NotEqual()->setValue(1)->assert($this->getContext('1')));
        self::assertTrue(new NotEqual()->setValue('2')->assert($this->getContext(1)));

        self::assertFalse(new NotEqual()->setValue([1])->assert($this->getContext([1])));
        self::assertTrue(new NotEqual()->setValue([1])->assert($this->getContext([2])));
    }

    public function testGreaterThanTest(): void
    {
        self::assertTrue(new GreaterThan()->setValue(1)->assert($this->getContext(2)));
        self::assertFalse(new GreaterThan()->setValue(2)->assert($this->getContext(1)));
    }

    public function testGreaterThanOrEqualTest(): void
    {
        self::assertTrue(new GreaterThanOrEqual()->setValue(1)->assert($this->getContext(2)));
        self::assertTrue(new GreaterThanOrEqual()->setValue(1)->assert($this->getContext(1)));
        self::assertFalse(new GreaterThanOrEqual()->setValue(2)->assert($this->getContext(1)));
    }

    public function testLowerThanTest(): void
    {
        self::assertFalse(new LowerThan()->setValue(1)->assert($this->getContext(2)));
        self::assertTrue(new LowerThan()->setValue(2)->assert($this->getContext(1)));
    }

    public function testLowerThanOrEqualTest(): void
    {
        self::assertFalse(new LowerThanOrEqual()->setValue(1)->assert($this->getContext(2)));
        self::assertTrue(new LowerThanOrEqual()->setValue(1)->assert($this->getContext(1)));
        self::assertTrue(new LowerThanOrEqual()->setValue(2)->assert($this->getContext(1)));
    }

    public function testStringContainTest(): void
    {
        $operator = new StringContain()->setValue('test');

        self::assertTrue($operator->assert($this->getContext('test')));
        self::assertTrue($operator->assert($this->getContext('atest')));
        self::assertTrue($operator->assert($this->getContext('testa')));
        self::assertFalse($operator->assert($this->getContext('aa')));
    }

    public function testStringNotContainTest(): void
    {
        $operator = new StringNotContain()->setValue('test');

        self::assertFalse($operator->assert($this->getContext('test')));
        self::assertFalse($operator->assert($this->getContext('atest')));
        self::assertFalse($operator->assert($this->getContext('testa')));
        self::assertTrue($operator->assert($this->getContext('aa')));
    }

    public function testArrayContainTest(): void
    {
        $operator = new ArrayContain()->setValue([1, 2, 3, 'a']);

        self::assertTrue($operator->assert($this->getContext(1)));
        self::assertTrue($operator->assert($this->getContext(2)));
        self::assertTrue($operator->assert($this->getContext(3)));
        self::assertTrue($operator->assert($this->getContext('a')));
        self::assertFalse($operator->assert($this->getContext('b')));
        self::assertFalse($operator->assert($this->getContext(4)));
    }

    public function testArrayNotContainTest(): void
    {
        $operator = new ArrayNotContain()->setValue([1, 2, 3, 'a']);

        self::assertFalse($operator->assert($this->getContext(1)));
        self::assertFalse($operator->assert($this->getContext(2)));
        self::assertFalse($operator->assert($this->getContext(3)));
        self::assertFalse($operator->assert($this->getContext('a')));
        self::assertTrue($operator->assert($this->getContext('b')));
        self::assertTrue($operator->assert($this->getContext(4)));
    }

    public function testArrayNumericInRangeTest(): void
    {
        $operator = new NumericInRange()->setValue([1, 5]);

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

    public function testStartsWithTest(): void
    {
        $operator = new StartsWith()->setValue('foo');

        self::assertTrue($operator->assert($this->getContext('foobar')));
        self::assertTrue($operator->assert($this->getContext('foo')));
        self::assertFalse($operator->assert($this->getContext('barfoo')));
        self::assertFalse($operator->assert($this->getContext('')));
    }

    public function testEndsWithTest(): void
    {
        $operator = new EndsWith()->setValue('bar');

        self::assertTrue($operator->assert($this->getContext('foobar')));
        self::assertTrue($operator->assert($this->getContext('bar')));
        self::assertFalse($operator->assert($this->getContext('barfoo')));
        self::assertFalse($operator->assert($this->getContext('')));
    }

    public function testMatchesRegexTest(): void
    {
        $operator = new MatchesRegex()->setValue('/^\d{3}$/');

        self::assertTrue($operator->assert($this->getContext('123')));
        self::assertFalse($operator->assert($this->getContext('12')));
        self::assertFalse($operator->assert($this->getContext('1234')));
        self::assertFalse($operator->assert($this->getContext('abc')));
    }

    public function testIsEmptyTest(): void
    {
        $operator = new IsEmpty();

        self::assertTrue($operator->assert($this->getContext('')));
        self::assertTrue($operator->assert($this->getContext([])));
        self::assertTrue($operator->assert($this->getNullContext()));
        self::assertFalse($operator->assert($this->getContext('text')));
        self::assertFalse($operator->assert($this->getContext(0)));
        self::assertFalse($operator->assert($this->getContext([1])));
    }

    public function testIsNullTest(): void
    {
        $operator = new IsNull();

        self::assertTrue($operator->assert($this->getNullContext()));
        self::assertFalse($operator->assert($this->getContext('')));
        self::assertFalse($operator->assert($this->getContext(0)));
        self::assertFalse($operator->assert($this->getContext(false)));
    }

    public function testIsInstanceOfTest(): void
    {
        $operator = new IsInstanceOf()->setValue(stdClass::class);

        self::assertTrue($operator->assert($this->getObjectContext(new stdClass())));
        self::assertFalse($operator->assert($this->getContext('not-an-object')));
        self::assertFalse($operator->assert($this->getObjectContext(new ArrayObject())));
    }

    public function testContainsKeyTest(): void
    {
        $operator = new ContainsKey()->setValue('name');

        self::assertTrue($operator->assert($this->getContext(['name' => 'Alice', 'age' => 30])));
        self::assertFalse($operator->assert($this->getContext(['age' => 30])));
        self::assertFalse($operator->assert($this->getContext('not-an-array')));
    }

    public function testCountEqualTest(): void
    {
        $operator = new CountEqual()->setValue(3);

        self::assertTrue($operator->assert($this->getContext([1, 2, 3])));
        self::assertFalse($operator->assert($this->getContext([1, 2])));
        self::assertFalse($operator->assert($this->getContext([])));
        self::assertFalse($operator->assert($this->getContext('not-an-array')));
    }

    public function testCountGreaterThanTest(): void
    {
        $operator = new CountGreaterThan()->setValue(2);

        self::assertTrue($operator->assert($this->getContext([1, 2, 3])));
        self::assertFalse($operator->assert($this->getContext([1, 2])));
        self::assertFalse($operator->assert($this->getContext([1])));
        self::assertFalse($operator->assert($this->getContext('not-an-array')));
    }

    private function getNullContext(): ContextInterface
    {
        return new class implements ContextInterface {
            public function getValue(): mixed
            {
                return null;
            }
        };
    }

    private function getObjectContext(object $object): ContextInterface
    {
        return new readonly class($object) implements ContextInterface {
            public function __construct(private object $object)
            {
            }

            public function getValue(): object
            {
                return $this->object;
            }
        };
    }

    private function getContext(int|string|array|bool $value): ContextInterface
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
