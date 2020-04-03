<?php

declare(strict_types=1);

namespace TheChoice\Node;

use TheChoice\Exception\LogicException;
use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Operator\OperatorInterface;

class Context extends AbstractChildNode implements Sortable
{
    public const STOP_IMMEDIATELY = 'immediately';

    protected $operator;
    protected $contextName;
    protected $priority;
    protected $params = [];
    protected $stoppableType;
    protected $modifiers = [];

    public static function getNodeName(): string
    {
        return 'context';
    }

    public function getSortableValue()
    {
        return $this->priority;
    }

    public function setPriority(int $priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return OperatorInterface|null
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return string|null
     */
    public function getContextName()
    {
        return $this->contextName;
    }

    public function setContextName(string $contextName) {
        $this->contextName = $contextName;
        return $this;
    }

    public function setOperator(OperatorInterface $operator){
        $this->operator = $operator;
        return $this;
    }

    public function getStoppableType()
    {
        return $this->stoppableType;
    }

    public function setStoppableType($type)
    {
        if ($type !== self::STOP_IMMEDIATELY) {
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

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    public function setModifiers(array $modifiers)
    {
        if ($this->checkModifiers($modifiers) === false) {
            throw new InvalidArgumentException('Context modifier must be string type');
        }
        $this->modifiers = $modifiers;
    }

    public static function getStopModes()
    {
        return [self::STOP_IMMEDIATELY];
    }

    private function checkModifiers(array $modifiers): bool
    {
        return array_reduce($modifiers, static function ($carry, $modifier) {
            return $carry && is_string($modifier);
        }, true);
    }
}
