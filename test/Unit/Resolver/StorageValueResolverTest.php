<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Resolver;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use TheChoice\Builder\ArrayBuilder;
use TheChoice\Builder\BuilderInterface;
use TheChoice\Builder\JsonBuilder;
use TheChoice\Builder\RuleBuilder;
use TheChoice\Container;
use TheChoice\Node\Root;
use TheChoice\NodeFactory\StorageValueResolver;
use TheChoice\Processor\RootProcessor;
use TheChoice\Tests\Integration\Contexts\DepositCount;
use TheChoice\Tests\Integration\Contexts\UserRole;
use TheChoice\Tests\Integration\Contexts\VisitCount;
use TheChoice\Tests\Integration\Contexts\WithdrawalCount;

final class StorageValueResolverTest extends TestCase
{
    // ─── StorageValueResolver unit tests ─────────────────────────────────

    public function testNonStringValueIsReturnedAsIs(): void
    {
        $builder = $this->makeBuilder([]);

        self::assertSame(42, StorageValueResolver::resolve(42, $builder));
        self::assertSame(3.14, StorageValueResolver::resolve(3.14, $builder));
        self::assertTrue(StorageValueResolver::resolve(true, $builder));
        self::assertNull(StorageValueResolver::resolve(null, $builder));
        self::assertSame([1, 2], StorageValueResolver::resolve([1, 2], $builder));
    }

    public function testStringWithoutDollarIsReturnedAsIs(): void
    {
        $builder = $this->makeBuilder(['myKey' => 99]);

        // 'myKey' is in storage but doesn't start with '$' — not a reference
        self::assertSame('myKey', StorageValueResolver::resolve('myKey', $builder));
    }

    public function testDollarStringNotInStorageIsReturnedAsIs(): void
    {
        $builder = $this->makeBuilder(['$other' => 5]);

        self::assertSame('$unknown', StorageValueResolver::resolve('$unknown', $builder));
    }

    public function testDollarStringResolvedToInteger(): void
    {
        $builder = $this->makeBuilder(['$threshold' => 100]);

        self::assertSame(100, StorageValueResolver::resolve('$threshold', $builder));
    }

    public function testDollarStringResolvedToFloat(): void
    {
        $builder = $this->makeBuilder(['$rate' => 0.15]);

        self::assertSame(0.15, StorageValueResolver::resolve('$rate', $builder));
    }

    public function testDollarStringResolvedToString(): void
    {
        $builder = $this->makeBuilder(['$role' => 'admin']);

        self::assertSame('admin', StorageValueResolver::resolve('$role', $builder));
    }

    public function testDollarStringResolvedToBool(): void
    {
        $builder = $this->makeBuilder(['$flag' => false]);

        self::assertFalse(StorageValueResolver::resolve('$flag', $builder));
    }

    public function testDollarStringResolvedToArray(): void
    {
        $builder = $this->makeBuilder(['$range' => [100, 500]]);

        self::assertSame([100, 500], StorageValueResolver::resolve('$range', $builder));
    }

    public function testEmptyStorageReturnsReferenceUnchanged(): void
    {
        $builder = $this->makeBuilder([]);

        self::assertSame('$anything', StorageValueResolver::resolve('$anything', $builder));
    }

    public function testBareDollarSignIsReturnedAsIs(): void
    {
        $builder = $this->makeBuilder(['$x' => 1]);

        // "$" alone is a valid string starting with '$', but it won't be in storage
        self::assertSame('$', StorageValueResolver::resolve('$', $builder));
    }

    public function testEmptyStringIsReturnedAsIs(): void
    {
        $builder = $this->makeBuilder(['$x' => 1]);

        self::assertSame('', StorageValueResolver::resolve('', $builder));
    }

    public function testArrayWithDollarStringsInsideIsNotResolved(): void
    {
        $builder = $this->makeBuilder(['$role1' => 'admin', '$role2' => 'manager']);

        // Array is not a string → is_string check returns false → pass through unchanged
        $input = ['$role1', '$role2'];
        self::assertSame($input, StorageValueResolver::resolve($input, $builder));
    }

    public function testStorageValueResolvedToNull(): void
    {
        // Storage can hold null as a value
        $root = new Root();
        // Cannot use setGlobal for null because it would still store it.
        // Using reflection to put null directly in storage:
        $reflStorage = new ReflectionProperty(Root::class, 'storage');
        $reflStorage->setValue($root, ['$nullable' => null]);

        $container = new Container([]);
        /** @var ArrayBuilder $builder */
        $builder = $container->get(ArrayBuilder::class);
        $builder->setRoot($root);

        self::assertNull(StorageValueResolver::resolve('$nullable', $builder));
    }

    // ─── Integration: factory resolution ─────────────────────────────────

    public function testContextOperatorValueResolvedFromStorageAtBuildTime(): void
    {
        // depositCount=2, $expectedCount=2 → equal → true
        $container = new Container(['depositCount' => DepositCount::class]);

        $root = RuleBuilder::root()
            ->storage(['$expectedCount' => 2])
            ->rules(RuleBuilder::context('depositCount')->equal(2))
            ->build()
        ;

        // Verify via JSON-parsed equivalent
        $json = json_encode([
            'node'    => 'root',
            'storage' => ['$expectedCount' => 2],
            'rules'   => [
                'node'     => 'context',
                'context'  => 'depositCount',
                'operator' => 'equal',
                'value'    => '$expectedCount',
            ],
        ]);

        /** @var JsonBuilder $builder */
        $builder = $container->get(JsonBuilder::class);
        /** @var RootProcessor $processor */
        $processor = $container->get(RootProcessor::class);

        $node = $builder->parse($json);
        $result = $processor->process($node);

        self::assertTrue($result);
    }

    public function testUnresolvableStorageReferenceKeepsStringValue(): void
    {
        $container = new Container(['withdrawalCount' => WithdrawalCount::class]);

        // $nonExistent is not in storage → value stays as string '$nonExistent'
        // withdrawalCount=0, equal('$nonExistent') → false
        $json = json_encode([
            'node'    => 'root',
            'storage' => [],
            'rules'   => [
                'node'     => 'context',
                'context'  => 'withdrawalCount',
                'operator' => 'equal',
                'value'    => '$nonExistent',
            ],
        ]);

        /** @var JsonBuilder $builder */
        $builder = $container->get(JsonBuilder::class);
        /** @var RootProcessor $processor */
        $processor = $container->get(RootProcessor::class);

        $node = $builder->parse($json);
        $result = $processor->process($node);

        // '0' !== '$nonExistent' → false
        self::assertFalse($result);
    }

    public function testSwitchCaseValueResolvedFromStorage(): void
    {
        $container = new Container(['userRole' => UserRole::class]);

        $json = json_encode([
            'node'    => 'root',
            'storage' => ['$adminRole' => 'admin'],
            'rules'   => [
                'node'    => 'switch',
                'context' => 'userRole',
                'cases'   => [
                    ['value' => '$adminRole', 'then' => ['node' => 'value', 'value' => 100]],
                ],
                'default' => ['node' => 'value', 'value' => 0],
            ],
        ]);

        /** @var JsonBuilder $builder */
        $builder = $container->get(JsonBuilder::class);
        /** @var RootProcessor $processor */
        $processor = $container->get(RootProcessor::class);

        $node = $builder->parse($json);
        $result = $processor->process($node);

        self::assertSame(100, $result);
    }

    public function testIntegerStorageValueUsedInGreaterThan(): void
    {
        $container = new Container(['visitCount' => VisitCount::class]);

        // visitCount=2, $minVisits=1 → greaterThan → true
        $json = json_encode([
            'node'    => 'root',
            'storage' => ['$minVisits' => 1],
            'rules'   => [
                'node'     => 'context',
                'context'  => 'visitCount',
                'operator' => 'greaterThan',
                'value'    => '$minVisits',
            ],
        ]);

        /** @var JsonBuilder $builder */
        $builder = $container->get(JsonBuilder::class);
        /** @var RootProcessor $processor */
        $processor = $container->get(RootProcessor::class);

        $result = $processor->process($builder->parse($json));

        self::assertTrue($result);
    }

    public function testArrayValueWithDollarStringsNotResolvedPerElement(): void
    {
        $container = new Container(['withdrawalCount' => WithdrawalCount::class]);

        // value is an array containing strings that look like $refs
        // The resolver only checks if the top-level value is_string → array passes through
        // withdrawalCount=0, arrayContain(['$role1', '$role2']) → false (0 not in array)
        $json = json_encode([
            'node'    => 'root',
            'storage' => ['$role1' => 'admin'],
            'rules'   => [
                'node'     => 'context',
                'context'  => 'withdrawalCount',
                'operator' => 'arrayContain',
                'value'    => ['$role1', '$role2'],
            ],
        ]);

        /** @var JsonBuilder $builder */
        $builder = $container->get(JsonBuilder::class);
        /** @var RootProcessor $processor */
        $processor = $container->get(RootProcessor::class);

        $result = $processor->process($builder->parse($json));

        // The array ['$role1', '$role2'] is NOT resolved element-wise,
        // so the operator compares 0 against ['$role1', '$role2'] → false
        self::assertFalse($result);
    }

    public function testNumericInRangeResolvedFromStorage(): void
    {
        $container = new Container(['visitCount' => VisitCount::class]);

        // visitCount=2, $range=[1,5] → numericInRange → true
        $json = json_encode([
            'node'    => 'root',
            'storage' => ['$range' => [1, 5]],
            'rules'   => [
                'node'     => 'context',
                'context'  => 'visitCount',
                'operator' => 'numericInRange',
                'value'    => '$range',
            ],
        ]);

        /** @var JsonBuilder $builder */
        $builder = $container->get(JsonBuilder::class);
        /** @var RootProcessor $processor */
        $processor = $container->get(RootProcessor::class);

        $result = $processor->process($builder->parse($json));

        self::assertTrue($result);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    private function makeBuilder(array $storage): BuilderInterface
    {
        $container = new Container([]);

        /** @var ArrayBuilder $builder */
        $builder = $container->get(ArrayBuilder::class);

        $root = new Root();
        foreach ($storage as $key => $value) {
            if (is_string($key)) {
                $root->setGlobal($key, $value);
            }
        }

        $builder->setRoot($root);

        return $builder;
    }
}
