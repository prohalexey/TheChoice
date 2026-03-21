<?php

declare(strict_types=1);

namespace TheChoice\Tests\Integration;

use PHPUnit\Framework\TestCase;
use TheChoice\Builder\JsonBuilder;
use TheChoice\Builder\YamlBuilder;
use TheChoice\Container;
use TheChoice\Processor\RootProcessor;
use TheChoice\Tests\Integration\Contexts\Action1;
use TheChoice\Tests\Integration\Contexts\Action2;
use TheChoice\Tests\Integration\Contexts\ActionReturnInt;
use TheChoice\Tests\Integration\Contexts\ActionWithParams;
use TheChoice\Tests\Integration\Contexts\ContextWithParams;
use TheChoice\Tests\Integration\Contexts\DepositCount;
use TheChoice\Tests\Integration\Contexts\DepositSum;
use TheChoice\Tests\Integration\Contexts\EmptyStringContext;
use TheChoice\Tests\Integration\Contexts\HasVipStatus;
use TheChoice\Tests\Integration\Contexts\InGroup;
use TheChoice\Tests\Integration\Contexts\TagsContext;
use TheChoice\Tests\Integration\Contexts\UserRole;
use TheChoice\Tests\Integration\Contexts\UtmSource;
use TheChoice\Tests\Integration\Contexts\VisitCount;
use TheChoice\Tests\Integration\Contexts\WithdrawalCount;

abstract class AbstractFormatIntegrationTestCase extends TestCase
{
    protected JsonBuilder|YamlBuilder $parser;

    protected RootProcessor $rootProcessor;

    protected string $testFilesDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testFilesDir = '';
        if ('TheChoice' === basename(getcwd())) {
            $this->testFilesDir = './test/Integration/';
        }

        $container = new Container([
            'visitCount'        => VisitCount::class,
            'hasVipStatus'      => HasVipStatus::class,
            'inGroup'           => InGroup::class,
            'withdrawalCount'   => WithdrawalCount::class,
            'depositCount'      => DepositCount::class,
            'depositSum'        => DepositSum::class,
            'userRole'          => UserRole::class,
            'utmSource'         => UtmSource::class,
            'contextWithParams' => ContextWithParams::class,
            'action1'           => Action1::class,
            'action2'           => Action2::class,
            'actionReturnInt'   => ActionReturnInt::class,
            'actionWithParams'  => ActionWithParams::class,
            'emptyString'       => EmptyStringContext::class,
            'tags'              => TagsContext::class,
        ]);

        $builderClass = $this->getBuilderClass();
        $this->parser = $container->get($builderClass);
        $this->rootProcessor = $container->get(RootProcessor::class);
    }

    abstract protected function getBuilderClass(): string;
}
