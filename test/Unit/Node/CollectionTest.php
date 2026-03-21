<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Node;

use PHPUnit\Framework\TestCase;
use TheChoice\Node\Collection;
use TheChoice\Node\Context;

final class CollectionTest extends TestCase
{
    public function testSortedDoesNotMutateOriginalOrder(): void
    {
        $collection = new Collection(Collection::TYPE_AND);

        $contextHigh = new Context();
        $contextHigh->setPriority(10);
        $contextHigh->setContextName('high');

        $contextLow = new Context();
        $contextLow->setPriority(1);
        $contextLow->setContextName('low');

        $contextMid = new Context();
        $contextMid->setPriority(5);
        $contextMid->setContextName('mid');

        $collection->add($contextHigh);
        $collection->add($contextLow);
        $collection->add($contextMid);

        $originalOrder = $collection->all();
        self::assertSame('high', $originalOrder[0]->getContextName());
        self::assertSame('low', $originalOrder[1]->getContextName());
        self::assertSame('mid', $originalOrder[2]->getContextName());

        // sorted() must return a sorted copy
        $sorted = $collection->sorted();
        self::assertSame('low', $sorted[0]->getContextName());
        self::assertSame('mid', $sorted[1]->getContextName());
        self::assertSame('high', $sorted[2]->getContextName());

        // Original order must be preserved after sorted() call
        $afterSort = $collection->all();
        self::assertSame('high', $afterSort[0]->getContextName());
        self::assertSame('low', $afterSort[1]->getContextName());
        self::assertSame('mid', $afterSort[2]->getContextName());
    }

    public function testSortedIsIdempotent(): void
    {
        $collection = new Collection(Collection::TYPE_OR);

        $contextA = new Context();
        $contextA->setPriority(3);
        $contextA->setContextName('a');

        $contextB = new Context();
        $contextB->setPriority(1);
        $contextB->setContextName('b');

        $collection->add($contextA);
        $collection->add($contextB);

        $first = $collection->sorted();
        $second = $collection->sorted();

        self::assertSame($first[0]->getContextName(), $second[0]->getContextName());
        self::assertSame($first[1]->getContextName(), $second[1]->getContextName());
    }
}
