<?php

declare(strict_types=1);

namespace TheChoice\Node;

use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Exception\LogicException;
use TheChoice\Operator\OperatorInterface;

class Context extends AbstractChildNode implements Sortable
{
    public const STOP_IMMEDIATELY = 'immediately';

    protected ?OperatorInterface $operator = null;

    protected ?string $contextName = null;

    protected int $priority = 0;

    protected array $params = [];

    protected ?string $stoppableType = null;

    /** @var array<string> */
    protected array $modifiers = [];

    public static function getNodeName(): string
    {
        return 'context';
    }

    public function getSortableValue(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getOperator(): ?OperatorInterface
    {
        return $this->operator;
    }

    public function getContextName(): ?string
    {
        return $this->contextName;
    }

    public function setContextName(string $contextName): static
    {
        $this->contextName = $contextName;

        return $this;
    }

    public function setOperator(OperatorInterface $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    public function getStoppableType(): ?string
    {
        return $this->stoppableType;
    }

    public function setStoppableType(?string $type): static
    {
        if (self::STOP_IMMEDIATELY !== $type) {
            throw new LogicException(sprintf('Stoppable type must be one of (%s). "%s" given', implode(', ', static::getStopModes()), $type));
        }

        $this->stoppableType = $type;

        return $this;
    }

    public function isStoppable(): bool
    {
        return null !== $this->stoppableType;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * @return array<string>
     */
    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    public function setModifiers(array $modifiers): void
    {
        if (false === $this->checkModifiers($modifiers)) {
            throw new InvalidArgumentException('Context modifier must be string type');
        }

        /** @var array<string> $modifiers */
        $this->modifiers = $modifiers;
    }

    public static function getStopModes(): array
    {
        return [self::STOP_IMMEDIATELY];
    }

    private function checkModifiers(array $modifiers): bool
    {
        return array_reduce($modifiers, static fn ($carry, $modifier): bool => $carry && is_string($modifier), true);
    }
}
