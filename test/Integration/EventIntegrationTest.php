<?php

declare(strict_types=1);

namespace TheChoice\Tests\Integration;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use TheChoice\Builder\JsonBuilder;
use TheChoice\Builder\RuleBuilder;
use TheChoice\Container;
use TheChoice\Engine\EngineReport;
use TheChoice\Engine\RuleEngine;
use TheChoice\Event\ContextEvaluatedEvent;
use TheChoice\Event\EngineRunAfterEvent;
use TheChoice\Event\EngineRunBeforeEvent;
use TheChoice\Event\RuleErrorEvent;
use TheChoice\Event\RuleFiredEvent;
use TheChoice\Event\SwitchResolvedEvent;
use TheChoice\Processor\RootProcessor;
use TheChoice\Tests\Integration\Contexts\Action1;
use TheChoice\Tests\Integration\Contexts\Action2;
use TheChoice\Tests\Integration\Contexts\ActionReturnInt;
use TheChoice\Tests\Integration\Contexts\DepositCount;
use TheChoice\Tests\Integration\Contexts\DepositSum;
use TheChoice\Tests\Integration\Contexts\HasVipStatus;
use TheChoice\Tests\Integration\Contexts\InGroup;
use TheChoice\Tests\Integration\Contexts\UserRole;
use TheChoice\Tests\Integration\Contexts\VisitCount;
use TheChoice\Tests\Integration\Contexts\WithdrawalCount;
use Throwable;

final class EventIntegrationTest extends TestCase
{
    private Container $container;

    private JsonBuilder $jsonBuilder;

    protected function setUp(): void
    {
        $this->container = new Container([
            'visitCount'      => VisitCount::class,
            'hasVipStatus'    => HasVipStatus::class,
            'inGroup'         => InGroup::class,
            'withdrawalCount' => WithdrawalCount::class,
            'depositCount'    => DepositCount::class,
            'depositSum'      => DepositSum::class,
            'userRole'        => UserRole::class,
            'action1'         => Action1::class,
            'action2'         => Action2::class,
            'actionReturnInt' => ActionReturnInt::class,
        ]);

        /** @var JsonBuilder $builder */
        $builder = $this->container->get(JsonBuilder::class);
        $this->jsonBuilder = $builder;
    }

    // ─── Engine lifecycle events ──────────────────────────────────────────

    public function testEngineRunBeforeEventIsDispatched(): void
    {
        $dispatcher = new EventDispatcher();
        $receivedEvent = null;

        $dispatcher->addListener(EngineRunBeforeEvent::class, static function (EngineRunBeforeEvent $event) use (&$receivedEvent): void {
            $receivedEvent = $event;
        });

        $engine = new RuleEngine($this->container, $dispatcher);
        $engine->addRule('rule1', $this->jsonBuilder->parse('{"node":"value","value":42}'), priority: 10);
        $engine->addRule('rule2', $this->jsonBuilder->parse('{"node":"value","value":1}'), priority: 5);

        $engine->run();

        self::assertInstanceOf(EngineRunBeforeEvent::class, $receivedEvent);
        self::assertCount(2, $receivedEvent->rules);
        // Should be sorted by priority (highest first)
        self::assertSame('rule1', $receivedEvent->rules[0]->name);
        self::assertSame('rule2', $receivedEvent->rules[1]->name);
    }

    public function testEngineRunAfterEventIsDispatched(): void
    {
        $dispatcher = new EventDispatcher();
        $receivedEvent = null;

        $dispatcher->addListener(EngineRunAfterEvent::class, static function (EngineRunAfterEvent $event) use (&$receivedEvent): void {
            $receivedEvent = $event;
        });

        $engine = new RuleEngine($this->container, $dispatcher);
        $engine->addRule('rule1', $this->jsonBuilder->parse('{"node":"value","value":42}'));

        $engine->run();

        self::assertInstanceOf(EngineRunAfterEvent::class, $receivedEvent);
        self::assertInstanceOf(EngineReport::class, $receivedEvent->report);
        self::assertGreaterThanOrEqual(0.0, $receivedEvent->elapsedMs);
        self::assertCount(1, $receivedEvent->report);
    }

    public function testRuleFiredEventIsDispatchedOnlyForFiredRules(): void
    {
        $dispatcher = new EventDispatcher();
        $firedNames = [];

        $dispatcher->addListener(RuleFiredEvent::class, static function (RuleFiredEvent $event) use (&$firedNames): void {
            $firedNames[] = $event->ruleName;
        });

        $engine = new RuleEngine($this->container, $dispatcher);
        // withdrawalCount == 0 → true (fires)
        $engine->addRule('should_fire', $this->jsonBuilder->parse('{"node":"context","context":"withdrawalCount","operator":"equal","value":0}'));
        // withdrawalCount == 99 → false (does not fire)
        $engine->addRule('should_skip', $this->jsonBuilder->parse('{"node":"context","context":"withdrawalCount","operator":"equal","value":99}'));
        // value 42 → fires
        $engine->addRule('also_fires', $this->jsonBuilder->parse('{"node":"value","value":42}'));

        $engine->run();

        self::assertSame(['should_fire', 'also_fires'], $firedNames);
    }

    public function testRuleFiredEventContainsElapsedMs(): void
    {
        $dispatcher = new EventDispatcher();
        $receivedEvent = null;

        $dispatcher->addListener(RuleFiredEvent::class, static function (RuleFiredEvent $event) use (&$receivedEvent): void {
            $receivedEvent = $event;
        });

        $engine = new RuleEngine($this->container, $dispatcher);
        $engine->addRule('rule1', $this->jsonBuilder->parse('{"node":"value","value":1}'));
        $engine->run();

        self::assertInstanceOf(RuleFiredEvent::class, $receivedEvent);
        self::assertSame('rule1', $receivedEvent->ruleName);
        self::assertGreaterThanOrEqual(0.0, $receivedEvent->elapsedMs);
        self::assertTrue($receivedEvent->result->fired);
    }

    public function testNoEventsDispatchedWithoutDispatcher(): void
    {
        // Engine without dispatcher — should work normally
        $engine = new RuleEngine($this->container);
        $engine->addRule('rule1', $this->jsonBuilder->parse('{"node":"value","value":42}'));

        $report = $engine->run();

        self::assertCount(1, $report);
        self::assertSame(42, $report->getResult('rule1')->result);
    }

    public function testEmptyEngineDispatchesBeforeAndAfterEvents(): void
    {
        $dispatcher = new EventDispatcher();
        $eventTypes = [];

        $dispatcher->addListener(EngineRunBeforeEvent::class, static function () use (&$eventTypes): void {
            $eventTypes[] = 'before';
        });
        $dispatcher->addListener(EngineRunAfterEvent::class, static function () use (&$eventTypes): void {
            $eventTypes[] = 'after';
        });

        $engine = new RuleEngine($this->container, $dispatcher);
        $engine->run();

        self::assertSame(['before', 'after'], $eventTypes);
    }

    // ─── Context evaluated events ─────────────────────────────────────────

    public function testContextEvaluatedEventIsDispatchedDuringProcess(): void
    {
        $dispatcher = new EventDispatcher();
        $receivedEvents = [];

        $dispatcher->addListener(ContextEvaluatedEvent::class, static function (ContextEvaluatedEvent $event) use (&$receivedEvents): void {
            $receivedEvents[] = $event;
        });

        $engine = new RuleEngine($this->container, $dispatcher);
        // withdrawalCount == 0 → true
        $engine->addRule('rule1', $this->jsonBuilder->parse('{"node":"context","context":"withdrawalCount","operator":"equal","value":0}'));
        $engine->run();

        self::assertCount(1, $receivedEvents);

        $event = $receivedEvents[0];
        self::assertSame('withdrawalCount', $event->contextName);
        self::assertSame(0, $event->contextValue);
        self::assertSame('equal', $event->operatorName);
        self::assertSame(0, $event->operatorValue);
        self::assertTrue($event->result);
    }

    public function testContextEvaluatedEventWithoutOperator(): void
    {
        $dispatcher = new EventDispatcher();
        $receivedEvents = [];

        $dispatcher->addListener(ContextEvaluatedEvent::class, static function (ContextEvaluatedEvent $event) use (&$receivedEvents): void {
            $receivedEvents[] = $event;
        });

        $engine = new RuleEngine($this->container, $dispatcher);
        // context without operator → returns raw value
        $engine->addRule('rule1', $this->jsonBuilder->parse('{"node":"context","context":"depositCount"}'));
        $engine->run();

        self::assertCount(1, $receivedEvents);

        $event = $receivedEvents[0];
        self::assertSame('depositCount', $event->contextName);
        self::assertSame(2, $event->contextValue);
        self::assertNull($event->operatorName);
        self::assertNull($event->operatorValue);
        self::assertSame(2, $event->result);
    }

    public function testContextEvaluatedEventWithCollection(): void
    {
        $dispatcher = new EventDispatcher();
        $contextNames = [];

        $dispatcher->addListener(ContextEvaluatedEvent::class, static function (ContextEvaluatedEvent $event) use (&$contextNames): void {
            $contextNames[] = $event->contextName;
        });

        $engine = new RuleEngine($this->container, $dispatcher);
        // AND collection with two contexts
        $engine->addRule('rule1', $this->jsonBuilder->parse(
            '{"node":"collection","type":"and","nodes":['
            . '{"node":"context","context":"withdrawalCount","operator":"equal","value":0},'
            . '{"node":"context","context":"visitCount","operator":"greaterThan","value":1}'
            . ']}',
        ));
        $engine->run();

        self::assertSame(['withdrawalCount', 'visitCount'], $contextNames);
    }

    public function testContextEvaluatedEventNotDispatchedWithoutDispatcher(): void
    {
        /** @var RootProcessor $rootProcessor */
        $rootProcessor = $this->container->get(RootProcessor::class);

        $root = RuleBuilder::root()
            ->rules(RuleBuilder::context('depositCount')->equal(2))
            ->build()
        ;

        // Should work fine without dispatcher
        $result = $rootProcessor->process($root);
        self::assertTrue($result);
    }

    // ─── Switch resolved events ───────────────────────────────────────────

    public function testSwitchResolvedEventDispatchedOnCaseMatch(): void
    {
        $dispatcher = new EventDispatcher();
        $receivedEvent = null;

        $dispatcher->addListener(SwitchResolvedEvent::class, static function (SwitchResolvedEvent $event) use (&$receivedEvent): void {
            $receivedEvent = $event;
        });

        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::switch('userRole')
                    ->case('admin', RuleBuilder::value(100))
                    ->case('manager', RuleBuilder::value(50))
                    ->default(RuleBuilder::value(0)),
            )
            ->build()
        ;

        $engine = new RuleEngine($this->container, $dispatcher);
        $engine->addRule('role_check', $root);
        $engine->run();

        self::assertInstanceOf(SwitchResolvedEvent::class, $receivedEvent);
        self::assertSame('userRole', $receivedEvent->contextName);
        self::assertSame('admin', $receivedEvent->contextValue);
        self::assertSame(0, $receivedEvent->matchedCaseIndex);
        self::assertSame(100, $receivedEvent->result);
    }

    public function testSwitchResolvedEventDispatchedOnDefault(): void
    {
        $dispatcher = new EventDispatcher();
        $receivedEvent = null;

        $dispatcher->addListener(SwitchResolvedEvent::class, static function (SwitchResolvedEvent $event) use (&$receivedEvent): void {
            $receivedEvent = $event;
        });

        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::switch('userRole')
                    ->case('manager', RuleBuilder::value(50))
                    ->default(RuleBuilder::value(0)),
            )
            ->build()
        ;

        $engine = new RuleEngine($this->container, $dispatcher);
        $engine->addRule('role_check', $root);
        $engine->run();

        self::assertInstanceOf(SwitchResolvedEvent::class, $receivedEvent);
        self::assertSame('userRole', $receivedEvent->contextName);
        self::assertSame('admin', $receivedEvent->contextValue);
        self::assertNull($receivedEvent->matchedCaseIndex);
        self::assertSame(0, $receivedEvent->result);
    }

    public function testSwitchResolvedEventDispatchedWithNoDefaultNoMatch(): void
    {
        $dispatcher = new EventDispatcher();
        $receivedEvent = null;

        $dispatcher->addListener(SwitchResolvedEvent::class, static function (SwitchResolvedEvent $event) use (&$receivedEvent): void {
            $receivedEvent = $event;
        });

        $root = RuleBuilder::root()
            ->rules(
                RuleBuilder::switch('userRole')
                    ->case('manager', RuleBuilder::value(50)),
            )
            ->build()
        ;

        $engine = new RuleEngine($this->container, $dispatcher);
        $engine->addRule('role_check', $root);
        $engine->run();

        self::assertInstanceOf(SwitchResolvedEvent::class, $receivedEvent);
        self::assertNull($receivedEvent->matchedCaseIndex);
        self::assertNull($receivedEvent->result);
    }

    // ─── Full event flow ──────────────────────────────────────────────────

    public function testFullEventFlowWithMultipleRules(): void
    {
        $dispatcher = new EventDispatcher();
        $eventLog = [];

        $dispatcher->addListener(EngineRunBeforeEvent::class, static function () use (&$eventLog): void {
            $eventLog[] = 'run.before';
        });
        $dispatcher->addListener(EngineRunAfterEvent::class, static function () use (&$eventLog): void {
            $eventLog[] = 'run.after';
        });
        $dispatcher->addListener(RuleFiredEvent::class, static function (RuleFiredEvent $event) use (&$eventLog): void {
            $eventLog[] = "fired:{$event->ruleName}";
        });
        $dispatcher->addListener(ContextEvaluatedEvent::class, static function (ContextEvaluatedEvent $event) use (&$eventLog): void {
            $eventLog[] = "ctx:{$event->contextName}";
        });

        $engine = new RuleEngine($this->container, $dispatcher);
        $engine->addRule('rule_true', $this->jsonBuilder->parse('{"node":"context","context":"withdrawalCount","operator":"equal","value":0}'));
        $engine->addRule('rule_false', $this->jsonBuilder->parse('{"node":"context","context":"withdrawalCount","operator":"equal","value":99}'));

        $engine->run();

        self::assertSame('run.before', $eventLog[0]);
        self::assertContains('ctx:withdrawalCount', $eventLog);
        self::assertContains('fired:rule_true', $eventLog);
        self::assertNotContains('fired:rule_false', $eventLog);
        self::assertSame('run.after', $eventLog[array_key_last($eventLog)]);
    }

    public function testEngineRunResultUnchangedWithEvents(): void
    {
        $dispatcher = new EventDispatcher();

        // Add listeners that don't modify anything
        $dispatcher->addListener(EngineRunBeforeEvent::class, static function (): void {});
        $dispatcher->addListener(EngineRunAfterEvent::class, static function (): void {});
        $dispatcher->addListener(RuleFiredEvent::class, static function (): void {});
        $dispatcher->addListener(ContextEvaluatedEvent::class, static function (): void {});

        $engine = new RuleEngine($this->container, $dispatcher);
        $engine->addRule('rule1', $this->jsonBuilder->parse('{"node":"context","context":"withdrawalCount","operator":"equal","value":0}'), priority: 10);
        $engine->addRule('rule2', $this->jsonBuilder->parse('{"node":"context","context":"withdrawalCount","operator":"equal","value":99}'), priority: 5);
        $engine->addRule('rule3', $this->jsonBuilder->parse('{"node":"value","value":42}'), priority: 1);

        $report = $engine->run();

        self::assertCount(3, $report);
        self::assertTrue($report->hasFired('rule1'));
        self::assertFalse($report->hasFired('rule2'));
        self::assertTrue($report->hasFired('rule3'));
        self::assertSame(42, $report->getResult('rule3')->result);
    }

    // ─── Edge cases ───────────────────────────────────────────────────────

    public function testListenerExceptionBubblesUp(): void
    {
        $dispatcher = new EventDispatcher();

        $dispatcher->addListener(RuleFiredEvent::class, static function (): never {
            throw new RuntimeException('Listener failed');
        });

        $engine = new RuleEngine($this->container, $dispatcher);
        $engine->addRule('rule1', $this->jsonBuilder->parse('{"node":"value","value":1}'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Listener failed');

        $engine->run();
    }

    public function testEventsWorkAlongsideProcessWithTrace(): void
    {
        $dispatcher = new EventDispatcher();
        $contextEvents = [];

        $dispatcher->addListener(ContextEvaluatedEvent::class, static function (ContextEvaluatedEvent $event) use (&$contextEvents): void {
            $contextEvents[] = $event;
        });

        /** @var RootProcessor $rootProcessor */
        $rootProcessor = $this->container->get(RootProcessor::class);
        $rootProcessor->setEventDispatcher($dispatcher);

        $root = RuleBuilder::root()
            ->rules(RuleBuilder::context('withdrawalCount')->equal(0))
            ->build()
        ;

        $trace = $rootProcessor->processWithTrace($root);

        // Both trace and events should work
        self::assertTrue($trace->getValue());
        self::assertStringContainsString('Context', $trace->explain());
        self::assertCount(1, $contextEvents);
        self::assertSame('withdrawalCount', $contextEvents[0]->contextName);
    }

    public function testStoppableContextFiresEventBeforeStop(): void
    {
        $dispatcher = new EventDispatcher();
        $contextEvents = [];

        $dispatcher->addListener(ContextEvaluatedEvent::class, static function (ContextEvaluatedEvent $event) use (&$contextEvents): void {
            $contextEvents[] = $event->contextName;
        });

        $engine = new RuleEngine($this->container, $dispatcher);
        // actionReturnInt has break:immediately — stops after first context in then-branch
        $engine->addRule('stoppable', $this->jsonBuilder->parse(
            '{"node":"condition","if":{"node":"context","context":"withdrawalCount","operator":"equal","value":0},'
            . '"then":{"node":"collection","type":"and","nodes":['
            . '{"node":"context","context":"actionReturnInt","break":"immediately"},'
            . '{"node":"context","context":"action2"}'
            . ']}}',
        ));
        $engine->run();

        // actionReturnInt should fire event, action2 should NOT (stopped before)
        self::assertContains('withdrawalCount', $contextEvents);
        self::assertContains('actionReturnInt', $contextEvents);
        self::assertNotContains('action2', $contextEvents);
    }

    public function testContextModifiersEventReportsRawContextValue(): void
    {
        $dispatcher = new EventDispatcher();
        $receivedEvent = null;

        $dispatcher->addListener(ContextEvaluatedEvent::class, static function (ContextEvaluatedEvent $event) use (&$receivedEvent): void {
            $receivedEvent = $event;
        });

        $engine = new RuleEngine($this->container, $dispatcher);
        // actionReturnInt returns 5, modifier doubles it: ($context * 2) = 10
        $engine->addRule('modifier_rule', $this->jsonBuilder->parse(
            '{"node":"context","context":"actionReturnInt","modifiers":["$context * 2"]}',
        ));
        $engine->run();

        self::assertInstanceOf(ContextEvaluatedEvent::class, $receivedEvent);
        // contextValue should be the RAW value (5), not the modified value
        self::assertSame(5, $receivedEvent->contextValue);
        // result should be the MODIFIED value (10)
        self::assertSame(10, $receivedEvent->result);
        self::assertNull($receivedEvent->operatorName);
    }

    public function testRuleErrorEventDispatchedOnException(): void
    {
        $dispatcher = new EventDispatcher();
        $receivedError = null;

        $dispatcher->addListener(RuleErrorEvent::class, static function (RuleErrorEvent $event) use (&$receivedError): void {
            $receivedError = $event;
        });

        $engine = new RuleEngine($this->container, $dispatcher);
        // Reference a non-existent context to trigger an exception
        $engine->addRule('bad_rule', $this->jsonBuilder->parse('{"node":"context","context":"nonExistentContext","operator":"equal","value":0}'));

        try {
            $engine->run();
        } catch (Throwable) {
            // Expected exception
        }

        self::assertInstanceOf(RuleErrorEvent::class, $receivedError);
        self::assertSame('bad_rule', $receivedError->ruleName);
        self::assertInstanceOf(Throwable::class, $receivedError->exception);
    }

    public function testRuleErrorEventExceptionIsReThrown(): void
    {
        $dispatcher = new EventDispatcher();

        $dispatcher->addListener(RuleErrorEvent::class, static function (): void {
            // Error handler does not suppress exception
        });

        $engine = new RuleEngine($this->container, $dispatcher);
        $engine->addRule('bad_rule', $this->jsonBuilder->parse('{"node":"context","context":"nonExistentContext","operator":"equal","value":0}'));

        $this->expectException(Throwable::class);
        $engine->run();
    }

    public function testContextWithModifiersAndOperatorReportsCorrectValues(): void
    {
        $dispatcher = new EventDispatcher();
        $receivedEvent = null;

        $dispatcher->addListener(ContextEvaluatedEvent::class, static function (ContextEvaluatedEvent $event) use (&$receivedEvent): void {
            $receivedEvent = $event;
        });

        $engine = new RuleEngine($this->container, $dispatcher);
        // actionReturnInt=5, modifier: $context*2=10, then greaterThan 5 → true
        $engine->addRule('mod_op_rule', $this->jsonBuilder->parse(
            '{"node":"context","context":"actionReturnInt","modifiers":["$context * 2"],"operator":"greaterThan","value":5}',
        ));
        $engine->run();

        self::assertInstanceOf(ContextEvaluatedEvent::class, $receivedEvent);
        self::assertSame(5, $receivedEvent->contextValue);
        self::assertSame('greaterThan', $receivedEvent->operatorName);
        self::assertSame(5, $receivedEvent->operatorValue);
        self::assertTrue($receivedEvent->result);
    }

    public function testOrCollectionShortCircuitFiresOnlyEvaluatedContexts(): void
    {
        $dispatcher = new EventDispatcher();
        $contextNames = [];

        $dispatcher->addListener(ContextEvaluatedEvent::class, static function (ContextEvaluatedEvent $event) use (&$contextNames): void {
            $contextNames[] = $event->contextName;
        });

        $engine = new RuleEngine($this->container, $dispatcher);
        // OR collection: first context is true → short-circuits, second not evaluated
        $engine->addRule('rule1', $this->jsonBuilder->parse(
            '{"node":"collection","type":"or","nodes":['
            . '{"node":"context","context":"withdrawalCount","operator":"equal","value":0},'
            . '{"node":"context","context":"visitCount","operator":"equal","value":999}'
            . ']}',
        ));
        $engine->run();

        // OR short-circuits after first true — second context NOT evaluated
        self::assertSame(['withdrawalCount'], $contextNames);
    }
}
