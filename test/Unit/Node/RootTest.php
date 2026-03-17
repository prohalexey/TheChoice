<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Node;

use PHPUnit\Framework\TestCase;
use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Node\Root;
use TheChoice\Node\Value;

final class RootTest extends TestCase
{
    private Root $root;

    protected function setUp(): void
    {
        $this->root = new Root();
    }

    // ─── setGlobal validation ───────────────────────────────────────────────

    public function testSetGlobalWithValidKeySucceeds(): void
    {
        $this->root->setGlobal('myVar', 42);
        self::assertSame(42, $this->root->getStorageValue('myVar'));
    }

    public function testSetGlobalWithDollarPrefixKeySucceeds(): void
    {
        $this->root->setGlobal('$myVar', 99);
        self::assertSame(99, $this->root->getStorageValue('$myVar'));
    }

    public function testSetGlobalWithNumericStartKeyThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->root->setGlobal('1invalid', 'value');
    }

    public function testSetGlobalWithEmptyKeyThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->root->setGlobal('', 'value');
    }

    public function testSetGlobalWithReservedContextKeyThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('reserved');
        $this->root->setGlobal('context', 'value');
    }

    public function testSetGlobalWithSpecialCharsOnlyThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->root->setGlobal('!!invalid!!', 'value');
    }

    // ─── result state ───────────────────────────────────────────────────────

    public function testHasResultReturnsFalseInitially(): void
    {
        self::assertFalse($this->root->hasResult());
    }

    public function testSetResultMakesHasResultTrue(): void
    {
        $this->root->setResult('some value');
        self::assertTrue($this->root->hasResult());
        self::assertSame('some value', $this->root->getResult());
    }

    public function testGetResultReturnsNullByDefault(): void
    {
        self::assertNull($this->root->getResult());
    }

    // ─── storage ────────────────────────────────────────────────────────────

    public function testGetStorageValueReturnsNullForMissingKey(): void
    {
        self::assertNull($this->root->getStorageValue('nonexistent'));
    }

    public function testGetStorageReturnsAllValues(): void
    {
        $this->root->setGlobal('a', 1);
        $this->root->setGlobal('b', 2);

        $storage = $this->root->getStorage();
        self::assertSame(['a' => 1, 'b' => 2], $storage);
    }

    // ─── rules ──────────────────────────────────────────────────────────────

    public function testSetAndGetRules(): void
    {
        $valueNode = new Value(123);
        $this->root->setRules($valueNode);

        self::assertSame($valueNode, $this->root->getRules());
    }

    // ─── node name ──────────────────────────────────────────────────────────

    public function testGetNodeNameReturnsRoot(): void
    {
        self::assertSame('root', Root::getNodeName());
    }
}
