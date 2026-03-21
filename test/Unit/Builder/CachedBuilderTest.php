<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Builder;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use TheChoice\Builder\CachedJsonBuilder;
use TheChoice\Builder\CachedYamlBuilder;
use TheChoice\Container;
use TheChoice\Node\Node;
use TheChoice\Node\Root;
use TheChoice\Processor\RootProcessor;

final class CachedBuilderTest extends TestCase
{
    private Container $container;

    /** @var CacheInterface&MockObject */
    private MockObject $cache;

    protected function setUp(): void
    {
        $this->container = new Container([]);
        $this->cache = $this->createMock(CacheInterface::class);
    }

    // ─── CachedJsonBuilder ───────────────────────────────────────────────────

    public function testJsonCacheMissParsesThenStores(): void
    {
        $json = '{"node":"value","value":42}';

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn(null)
        ;

        $this->cache->expects($this->once())
            ->method('set')
            ->with(
                $this->matchesRegularExpression('/^the-choice\.[a-f0-9]{32}$/'),
                $this->isString(),
                null,
            )
        ;

        $builder = new CachedJsonBuilder($this->container, $this->cache);
        $node = $builder->parse($json);

        self::assertInstanceOf(Root::class, $node);
    }

    public function testJsonCacheHitReturnsDeserializedNodeWithoutReparsing(): void
    {
        $json = '{"node":"value","value":99}';

        $container = new Container([]);
        $fresh = new CachedJsonBuilder($container, $this->createMock(CacheInterface::class))->parse($json);
        $serialized = serialize($fresh);

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn($serialized)
        ;

        $this->cache->expects($this->never())
            ->method('set')
        ;

        $builder = new CachedJsonBuilder($this->container, $this->cache);
        $node = $builder->parse($json);

        self::assertInstanceOf(Root::class, $node);
    }

    public function testJsonSameContentReturnsDifferentObjectInstances(): void
    {
        $json = '{"node":"value","value":5}';
        $serialized = null;

        $this->cache->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(static function () use (&$serialized): ?string {
                return $serialized;
            })
        ;

        $this->cache->expects($this->once())
            ->method('set')
            ->willReturnCallback(static function (string $key, string $value) use (&$serialized): bool {
                $serialized = $value;

                return true;
            })
        ;

        $builder = new CachedJsonBuilder($this->container, $this->cache);

        $node1 = $builder->parse($json);
        $node2 = $builder->parse($json);

        self::assertNotSame($node1, $node2, 'Each cache hit must return a fresh deserialized copy');
    }

    public function testJsonCorruptedCacheEntryFallsBackToFreshParse(): void
    {
        $json = '{"node":"value","value":7}';

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn('not-valid-serialized-data')
        ;

        $this->cache->expects($this->once())
            ->method('set')
        ;

        $builder = new CachedJsonBuilder($this->container, $this->cache);
        $node = $builder->parse($json);

        self::assertInstanceOf(Node::class, $node);
    }

    public function testJsonCustomKeyPrefixIsUsed(): void
    {
        $json = '{"node":"value","value":1}';

        $this->cache->expects($this->once())
            ->method('get')
            ->with($this->stringStartsWith('my-prefix.'))
            ->willReturn(null)
        ;

        $this->cache->expects($this->once())
            ->method('set')
            ->with($this->stringStartsWith('my-prefix.'))
        ;

        $builder = new CachedJsonBuilder($this->container, $this->cache, null, 'my-prefix.');
        $builder->parse($json);
    }

    public function testJsonTtlIsPassedToCache(): void
    {
        $json = '{"node":"value","value":1}';

        $this->cache->method('get')->willReturn(null);

        $this->cache->expects($this->once())
            ->method('set')
            ->with($this->anything(), $this->anything(), 3600)
        ;

        $builder = new CachedJsonBuilder($this->container, $this->cache, ttl: 3600);
        $builder->parse($json);
    }

    // ─── CachedYamlBuilder ───────────────────────────────────────────────────

    public function testYamlCacheMissParsesThenStores(): void
    {
        $yaml = "node: value\nvalue: 42\n";

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn(null)
        ;

        $this->cache->expects($this->once())
            ->method('set')
            ->with(
                $this->matchesRegularExpression('/^the-choice\.[a-f0-9]{32}$/'),
                $this->isString(),
                null,
            )
        ;

        $builder = new CachedYamlBuilder($this->container, $this->cache);
        $node = $builder->parse($yaml);

        self::assertInstanceOf(Root::class, $node);
    }

    public function testYamlCacheHitReturnsDeserializedNodeWithoutReparsing(): void
    {
        $yaml = "node: value\nvalue: 99\n";

        $fresh = new CachedYamlBuilder(new Container([]), $this->createMock(CacheInterface::class))->parse($yaml);
        $serialized = serialize($fresh);

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn($serialized)
        ;

        $this->cache->expects($this->never())
            ->method('set')
        ;

        $builder = new CachedYamlBuilder($this->container, $this->cache);
        $node = $builder->parse($yaml);

        self::assertInstanceOf(Root::class, $node);
    }

    public function testYamlTtlIsPassedToCache(): void
    {
        $yaml = "node: value\nvalue: 1\n";

        $this->cache->method('get')->willReturn(null);

        $this->cache->expects($this->once())
            ->method('set')
            ->with($this->anything(), $this->anything(), 7200)
        ;

        $builder = new CachedYamlBuilder($this->container, $this->cache, ttl: 7200);
        $builder->parse($yaml);
    }

    // ─── parseFile integration ───────────────────────────────────────────────

    public function testJsonParseFileDelegatesToCachedParse(): void
    {
        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn(null)
        ;

        $this->cache->expects($this->once())
            ->method('set')
        ;

        $builder = new CachedJsonBuilder($this->container, $this->cache);
        $node = $builder->parseFile(__DIR__ . '/../../Integration/Json/testNodeValue.json');

        self::assertInstanceOf(Root::class, $node);
    }

    public function testYamlParseFileDelegatesToCachedParse(): void
    {
        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn(null)
        ;

        $this->cache->expects($this->once())
            ->method('set')
        ;

        $builder = new CachedYamlBuilder($this->container, $this->cache);
        $node = $builder->parseFile(__DIR__ . '/../../Integration/Yaml/testNodeValue.yaml');

        self::assertInstanceOf(Root::class, $node);
    }

    // ─── Deserialized tree can be processed ─────────────────────────────────

    public function testDeserializedJsonNodeTreeIsProcessable(): void
    {
        $json = '{"node":"value","value":42}';

        $serialized = null;
        $this->cache->method('get')
            ->willReturnCallback(static function () use (&$serialized): ?string {
                return $serialized;
            })
        ;
        $this->cache->method('set')
            ->willReturnCallback(static function (string $key, string $value) use (&$serialized): bool {
                $serialized = $value;

                return true;
            })
        ;

        $builder = new CachedJsonBuilder($this->container, $this->cache);

        // First call: miss → parse → store
        $node1 = $builder->parse($json);
        // Second call: hit → deserialize
        $node2 = $builder->parse($json);

        // Both must return the value node result when processed
        $processor = $this->container->get(RootProcessor::class);
        self::assertSame(42, $processor->process($node1));
        self::assertSame(42, $processor->process($node2));
    }
}
