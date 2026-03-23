<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Node;

use PHPUnit\Framework\TestCase;
use TheChoice\Node\Value;

/**
 * Tests for AbstractNode behaviour (tested through the concrete Value subclass).
 */
final class AbstractNodeTest extends TestCase
{
    // ─── description default (after fix: initialized to '') ───────────────

    public function testDescriptionDefaultsToEmptyString(): void
    {
        $node = new Value(1);

        self::assertSame('', $node->getDescription());
    }

    public function testSetAndGetDescription(): void
    {
        $node = new Value(1);
        $node->setDescription('my description');

        self::assertSame('my description', $node->getDescription());
    }

    public function testSetDescriptionReturnsSelf(): void
    {
        $node = new Value(1);
        $result = $node->setDescription('test');

        self::assertSame($node, $result);
    }

    public function testSetDescriptionOverwritesPreviousValue(): void
    {
        $node = new Value(1);
        $node->setDescription('first');
        $node->setDescription('second');

        self::assertSame('second', $node->getDescription());
    }

    public function testDescriptionCanBeEmptyString(): void
    {
        $node = new Value(1);
        $node->setDescription('some value');
        $node->setDescription('');

        self::assertSame('', $node->getDescription());
    }
}
