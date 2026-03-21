<?php

declare(strict_types=1);

namespace TheChoice\Builder;

use TheChoice\Node\Context;
use TheChoice\Operator\ArrayContain;
use TheChoice\Operator\ArrayNotContain;
use TheChoice\Operator\ContainsKey;
use TheChoice\Operator\CountEqual;
use TheChoice\Operator\CountGreaterThan;
use TheChoice\Operator\EndsWith;
use TheChoice\Operator\Equal;
use TheChoice\Operator\GreaterThan;
use TheChoice\Operator\GreaterThanOrEqual;
use TheChoice\Operator\IsEmpty;
use TheChoice\Operator\IsInstanceOf;
use TheChoice\Operator\IsNull;
use TheChoice\Operator\LowerThan;
use TheChoice\Operator\LowerThanOrEqual;
use TheChoice\Operator\MatchesRegex;
use TheChoice\Operator\NotEqual;
use TheChoice\Operator\NumericInRange;
use TheChoice\Operator\OperatorInterface;
use TheChoice\Operator\StartsWith;
use TheChoice\Operator\StringContain;
use TheChoice\Operator\StringNotContain;

final class ContextBuilder implements NodeBuilderInterface
{
    private ?OperatorInterface $operator = null;

    /** @var array<string> */
    private array $modifiers = [];

    /** @var array<mixed> */
    private array $params = [];

    private int $priority = 0;

    private ?string $description = null;

    private bool $stoppable = false;

    public function __construct(private readonly string $contextName)
    {
    }

    // ─── Operators ────────────────────────────────────────────────────────

    public function equal(mixed $value): self
    {
        $this->operator = new Equal()->setValue($value);

        return $this;
    }

    public function notEqual(mixed $value): self
    {
        $this->operator = new NotEqual()->setValue($value);

        return $this;
    }

    public function greaterThan(mixed $value): self
    {
        $this->operator = new GreaterThan()->setValue($value);

        return $this;
    }

    public function greaterThanOrEqual(mixed $value): self
    {
        $this->operator = new GreaterThanOrEqual()->setValue($value);

        return $this;
    }

    public function lowerThan(mixed $value): self
    {
        $this->operator = new LowerThan()->setValue($value);

        return $this;
    }

    public function lowerThanOrEqual(mixed $value): self
    {
        $this->operator = new LowerThanOrEqual()->setValue($value);

        return $this;
    }

    /**
     * @param array{0: int|float, 1: int|float} $range [min, max] inclusive
     */
    public function numericInRange(array $range): self
    {
        $this->operator = new NumericInRange()->setValue($range);

        return $this;
    }

    public function arrayContain(mixed $value): self
    {
        $this->operator = new ArrayContain()->setValue($value);

        return $this;
    }

    public function arrayNotContain(mixed $value): self
    {
        $this->operator = new ArrayNotContain()->setValue($value);

        return $this;
    }

    public function containsKey(string|int $key): self
    {
        $this->operator = new ContainsKey()->setValue($key);

        return $this;
    }

    public function countEqual(int $count): self
    {
        $this->operator = new CountEqual()->setValue($count);

        return $this;
    }

    public function countGreaterThan(int $count): self
    {
        $this->operator = new CountGreaterThan()->setValue($count);

        return $this;
    }

    public function stringContain(string $substring): self
    {
        $this->operator = new StringContain()->setValue($substring);

        return $this;
    }

    public function stringNotContain(string $substring): self
    {
        $this->operator = new StringNotContain()->setValue($substring);

        return $this;
    }

    public function startsWith(string $prefix): self
    {
        $this->operator = new StartsWith()->setValue($prefix);

        return $this;
    }

    public function endsWith(string $suffix): self
    {
        $this->operator = new EndsWith()->setValue($suffix);

        return $this;
    }

    public function matchesRegex(string $pattern): self
    {
        $this->operator = new MatchesRegex()->setValue($pattern);

        return $this;
    }

    /** Does not require a comparison value. */
    public function isEmpty(): self
    {
        $this->operator = new IsEmpty();

        return $this;
    }

    /** Does not require a comparison value. */
    public function isNull(): self
    {
        $this->operator = new IsNull();

        return $this;
    }

    public function isInstanceOf(string $class): self
    {
        $this->operator = new IsInstanceOf()->setValue($class);

        return $this;
    }

    // ─── Modifiers ────────────────────────────────────────────────────────

    /** Appends a single mathematical modifier expression. */
    public function modifier(string $expression): self
    {
        $this->modifiers[] = $expression;

        return $this;
    }

    /**
     * Replaces the modifier list with the given expressions.
     *
     * @param array<string> $expressions
     */
    public function modifiers(array $expressions): self
    {
        $this->modifiers = $expressions;

        return $this;
    }

    // ─── Other properties ─────────────────────────────────────────────────

    /**
     * @param array<mixed> $params
     */
    public function params(array $params): self
    {
        $this->params = $params;

        return $this;
    }

    public function priority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Marks the context as stoppable: when the context is evaluated its result
     * is stored on the Root node and evaluation stops immediately.
     */
    public function stoppable(): self
    {
        $this->stoppable = true;

        return $this;
    }

    // ─── Build ────────────────────────────────────────────────────────────

    public function build(): Context
    {
        $node = new Context();
        $node->setContextName($this->contextName);

        if (null !== $this->operator) {
            $node->setOperator($this->operator);
        }

        if ([] !== $this->modifiers) {
            $node->setModifiers($this->modifiers);
        }

        if ([] !== $this->params) {
            $node->setParams($this->params);
        }

        if (0 !== $this->priority) {
            $node->setPriority($this->priority);
        }

        if (null !== $this->description) {
            $node->setDescription($this->description);
        }

        if ($this->stoppable) {
            $node->setStoppableType(Context::STOP_IMMEDIATELY);
        }

        return $node;
    }
}
