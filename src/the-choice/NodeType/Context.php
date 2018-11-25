<?php

namespace TheChoice\NodeType;

use TheChoice\Contracts\OperatorInterface;
use TheChoice\Contracts\Sortable;

final class Context implements Sortable
{
    const STOP_ALWAYS = 'always';

    private $_operator;
    private $_contextName;
    private $_description = '';
    private $_priority;
    private $_params = [];
    private $_stoppableType;
    private $_modifiers = [];

    public function getDescription(): string
    {
        return $this->_description;
    }

    public function setDescription(string $description)
    {
        $this->_description = $description;
        return $this;
    }

    public function getSortableValue()
    {
        return $this->_priority;
    }

    public function setPriority(int $priority)
    {
        $this->_priority = $priority;
        return $this;
    }

    /**
     * @return OperatorInterface|null
     */
    public function getOperator()
    {
        return $this->_operator;
    }

    /**
     * @return string|null
     */
    public function getContextName()
    {
        return $this->_contextName;
    }

    public function setContextName(string $contextName) {
        $this->_contextName = $contextName;
        return $this;
    }

    public function setOperator(OperatorInterface $operator){
        $this->_operator = $operator;
        return $this;
    }

    public function getStoppableType()
    {
        return $this->_stoppableType;
    }

    public function setStoppableType($type)
    {
        $this->_stoppableType = $type;

        return $this;
    }

    public function isStoppable(): bool
    {
        return null !== $this->_stoppableType;
    }

    public function getParams(): array
    {
        return $this->_params;
    }

    public function setParams(array $params)
    {
        $this->_params = $params;
    }

    public function getModifiers()
    {
        return $this->_modifiers;
    }

    public function setModifiers(array $modifiers)
    {
        if ($this->checkModifiers($modifiers) === false) {
            throw new \InvalidArgumentException('Context modifier must be string type');
        }
        $this->_modifiers = $modifiers;
    }

    private function checkModifiers(array $modifiers): bool
    {
        return array_reduce($modifiers, function ($carry, $modifier) {
            return $carry && \is_string($modifier);
        }, true);
    }
}
