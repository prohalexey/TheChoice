<?php

declare(strict_types=1);

namespace TheChoice\Builder;

use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Node\SwitchCase;
use TheChoice\Node\SwitchNode;
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

final class SwitchBuilder implements NodeBuilderInterface
{
    /** @var array<array{operator: OperatorInterface, thenBuilder: NodeBuilderInterface}> */
    private array $cases = [];

    private ?NodeBuilderInterface $defaultBuilder = null;

    private ?string $description = null;

    public function __construct(private readonly string $contextName)
    {
    }

    /**
     * Adds a case that matches when the context value equals $value (strict equality).
     * This is a shortcut for {@see caseWith()} using the {@see Equal} operator.
     */
    public function case(mixed $value, NodeBuilderInterface $thenBuilder): self
    {
        return $this->caseWith(new Equal()->setValue($value), $thenBuilder);
    }

    /**
     * Adds a case using a named built-in operator.
     * The operator is instantiated internally and $value is set via {@see OperatorInterface::setValue()}.
     */
    public function caseOp(string $operatorName, mixed $value, NodeBuilderInterface $thenBuilder): self
    {
        $operator = self::createOperator($operatorName);
        $operator->setValue($value);

        return $this->caseWith($operator, $thenBuilder);
    }

    /**
     * Adds a case with an already-configured operator instance.
     * Use this for custom operators or when the operator has already been set up.
     */
    public function caseWith(OperatorInterface $operator, NodeBuilderInterface $thenBuilder): self
    {
        $this->cases[] = [
            'operator'    => $operator,
            'thenBuilder' => $thenBuilder,
        ];

        return $this;
    }

    /** Sets the fallback branch executed when no case matches. */
    public function default(NodeBuilderInterface $thenBuilder): self
    {
        $this->defaultBuilder = $thenBuilder;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function build(): SwitchNode
    {
        $cases = [];
        foreach ($this->cases as $entry) {
            $cases[] = new SwitchCase($entry['operator'], $entry['thenBuilder']->build());
        }

        $defaultNode = $this->defaultBuilder?->build();

        $node = new SwitchNode($this->contextName, $cases, $defaultNode);

        if (null !== $this->description) {
            $node->setDescription($this->description);
        }

        return $node;
    }

    private static function createOperator(string $name): OperatorInterface
    {
        return match ($name) {
            'equal'              => new Equal(),
            'notEqual'           => new NotEqual(),
            'greaterThan'        => new GreaterThan(),
            'greaterThanOrEqual' => new GreaterThanOrEqual(),
            'lowerThan'          => new LowerThan(),
            'lowerThanOrEqual'   => new LowerThanOrEqual(),
            'numericInRange'     => new NumericInRange(),
            'arrayContain'       => new ArrayContain(),
            'arrayNotContain'    => new ArrayNotContain(),
            'containsKey'        => new ContainsKey(),
            'countEqual'         => new CountEqual(),
            'countGreaterThan'   => new CountGreaterThan(),
            'stringContain'      => new StringContain(),
            'stringNotContain'   => new StringNotContain(),
            'startsWith'         => new StartsWith(),
            'endsWith'           => new EndsWith(),
            'matchesRegex'       => new MatchesRegex(),
            'isEmpty'            => new IsEmpty(),
            'isNull'             => new IsNull(),
            'isInstanceOf'       => new IsInstanceOf(),
            default              => throw new InvalidArgumentException(
                sprintf('Unknown operator "%s". Use caseWith() to supply a custom OperatorInterface instance.', $name),
            ),
        };
    }
}
