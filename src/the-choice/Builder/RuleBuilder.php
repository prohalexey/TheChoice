<?php

declare(strict_types=1);

namespace TheChoice\Builder;

use TheChoice\Node\Collection;
use TheChoice\Node\Condition;
use TheChoice\Node\Context;
use TheChoice\Node\Node;
use TheChoice\Node\Root;
use TheChoice\Node\SwitchNode;
use TheChoice\Node\Value;

/**
 * Static factory (entry point) for the fluent rule-building DSL.
 *
 * Every method returns a typed builder that accumulates configuration through
 * chained setter calls and materialises the corresponding {@see Node}
 * via {@see NodeBuilderInterface::build()}.
 *
 * To execute a rule, always finalise the tree with {@see RuleBuilder::root()} so that
 * the Root reference is propagated to all child nodes before passing to
 * {@see \TheChoice\Processor\RootProcessor::process()}.
 *
 * Example:
 * ```php
 * $root = RuleBuilder::root()
 *     ->rules(
 *         RuleBuilder::condition()
 *             ->if(
 *                 RuleBuilder::collection('and')
 *                     ->add(RuleBuilder::context('withdrawalCount')->equal(0))
 *                     ->add(RuleBuilder::context('inGroup')->arrayContain(['vip']))
 *             )
 *             ->then(
 *                 RuleBuilder::context('getDepositSum')->modifier('$context * 0.1')
 *             )
 *             ->else(RuleBuilder::value(5))
 *     )
 *     ->build();
 *
 * $result = $rootProcessor->process($root);
 * ```
 */
final class RuleBuilder
{
    /** Returns a builder for a static {@see Value} node. */
    public static function value(mixed $value): ValueBuilder
    {
        return new ValueBuilder($value);
    }

    /** Returns a builder for a {@see Context} node. */
    public static function context(string $contextName): ContextBuilder
    {
        return new ContextBuilder($contextName);
    }

    /** Returns a builder for a {@see Condition} node. */
    public static function condition(): ConditionBuilder
    {
        return new ConditionBuilder();
    }

    /**
     * Returns a builder for a {@see Collection} node.
     *
     * @param string $type One of {@see Collection::TYPE_AND}, {@see Collection::TYPE_OR},
     *                     {@see Collection::TYPE_NOT}, {@see Collection::TYPE_AT_LEAST},
     *                     {@see Collection::TYPE_EXACTLY}
     */
    public static function collection(string $type): CollectionBuilder
    {
        return new CollectionBuilder($type);
    }

    /** Returns a builder for a {@see SwitchNode} node. */
    public static function switch(string $contextName): SwitchBuilder
    {
        return new SwitchBuilder($contextName);
    }

    /**
     * Returns a builder for a {@see Root} node.
     * Always use this as the outermost builder — {@see RootBuilder::build()} propagates
     * the Root reference to all child nodes.
     */
    public static function root(): RootBuilder
    {
        return new RootBuilder();
    }
}
