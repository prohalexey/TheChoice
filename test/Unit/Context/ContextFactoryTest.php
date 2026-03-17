<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Context;

use PHPUnit\Framework\TestCase;
use stdClass;
use TheChoice\Context\ContextFactory;
use TheChoice\Context\ContextInterface;
use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Node\Context;
use TheChoice\Node\Root;

final class ContextFactoryTest extends TestCase
{
    // ─── Context resolution from class string ─────────────────────────────

    public function testCreatesContextFromClassName(): void
    {
        $factory = new ContextFactory([
            'myCtx' => SimpleTestContext::class,
        ]);

        $context = $factory->createContextFromContextNode(
            $this->makeContextNode('myCtx'),
        );

        self::assertInstanceOf(ContextInterface::class, $context);
        self::assertSame(42, $context->getValue());
    }

    // ─── Context resolution from object ──────────────────────────────────

    public function testCreatesContextFromObject(): void
    {
        $obj = new SimpleTestContext();
        $factory = new ContextFactory(['myCtx' => $obj]);

        $context = $factory->createContextFromContextNode(
            $this->makeContextNode('myCtx'),
        );

        self::assertSame(42, $context->getValue());
    }

    // ─── Context resolution from callable ────────────────────────────────

    public function testCreatesContextFromCallable(): void
    {
        // Use ContextFactory directly (not via Container) to pass a Closure.
        // The callable returns the raw value — ContextFactory wraps it in CallableContext.
        $factory = new ContextFactory([
            'myCtx' => static fn (): int => 42,
        ]);

        $context = $factory->createContextFromContextNode(
            $this->makeContextNode('myCtx'),
        );

        self::assertSame(42, $context->getValue());
    }

    // ─── Errors ──────────────────────────────────────────────────────────

    public function testThrowsWhenContextNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not found');

        $factory = new ContextFactory([]);
        $factory->createContextFromContextNode(
            $this->makeContextNode('nonexistent'),
        );
    }

    public function testThrowsWhenObjectDoesNotImplementContextInterface(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $factory = new ContextFactory([
            'bad' => new stdClass(),
        ]);

        $factory->createContextFromContextNode(
            $this->makeContextNode('bad'),
        );
    }

    public function testThrowsWhenClassDoesNotImplementContextInterface(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $factory = new ContextFactory([
            'bad' => stdClass::class,
        ]);

        $factory->createContextFromContextNode(
            $this->makeContextNode('bad'),
        );
    }

    // ─── Params injection ────────────────────────────────────────────────

    public function testInjectsParamsViaPublicProperty(): void
    {
        $factory = new ContextFactory([
            'ctx' => ContextWithPublicProp::class,
        ]);

        $context = $factory->createContextFromContextNode(
            $this->makeContextNode('ctx', ['publicProp' => 'hello']),
        );

        self::assertSame('hello', $context->getValue());
    }

    public function testInjectsParamsViaSetter(): void
    {
        $factory = new ContextFactory([
            'ctx' => ContextWithSetter::class,
        ]);

        $context = $factory->createContextFromContextNode(
            $this->makeContextNode('ctx', ['name' => 'world']),
        );

        self::assertSame('world', $context->getValue());
    }

    private function makeContextNode(string $contextName, array $params = []): Context
    {
        $root = new Root();
        $node = new Context();
        $node->setRoot($root);
        $node->setContextName($contextName);
        $node->setParams($params);

        return $node;
    }
}

// ─── Stubs ────────────────────────────────────────────────────────────────

class SimpleTestContext implements ContextInterface
{
    public function getValue(): int
    {
        return 42;
    }
}

class ContextWithPublicProp implements ContextInterface
{
    public string $publicProp = '';

    public function getValue(): string
    {
        return $this->publicProp;
    }
}

class ContextWithSetter implements ContextInterface
{
    private string $name = '';

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getValue(): string
    {
        return $this->name;
    }
}
