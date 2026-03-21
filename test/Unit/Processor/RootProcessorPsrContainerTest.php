<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Processor;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use TheChoice\Node\Root;
use TheChoice\Node\Value;
use TheChoice\Processor\ProcessorResolver;
use TheChoice\Processor\ProcessorResolverInterface;
use TheChoice\Processor\RootProcessor;
use TheChoice\Processor\ValueProcessor;

final class RootProcessorPsrContainerTest extends TestCase
{
    public function testRootProcessorWorksWithGenericPsrContainer(): void
    {
        $rootNode = new Root();
        $rootNode->setRules(new Value(123));

        $resolver = new ProcessorResolver();
        $rootProcessor = new RootProcessor();
        $valueProcessor = new ValueProcessor();

        $container = new readonly class($resolver, $rootProcessor, $valueProcessor) implements ContainerInterface {
            public function __construct(
                private ProcessorResolverInterface $resolver,
                private RootProcessor $rootProcessor,
                private ValueProcessor $valueProcessor,
            ) {
            }

            public function get(string $id): object
            {
                return match ($id) {
                    ProcessorResolverInterface::class => $this->resolver,
                    RootProcessor::class              => $this->rootProcessor,
                    ValueProcessor::class             => $this->valueProcessor,
                    default                           => throw new InvalidArgumentException(sprintf('Service "%s" not found', $id)),
                };
            }

            public function has(string $id): bool
            {
                return in_array($id, [
                    ProcessorResolverInterface::class,
                    RootProcessor::class,
                    ValueProcessor::class,
                ], true);
            }
        };

        $rootProcessor->setContainer($container);
        $valueProcessor->setContainer($container);

        $result = $rootProcessor->process($rootNode);

        self::assertSame(123, $result);
    }
}
