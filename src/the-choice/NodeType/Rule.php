<?php

namespace TheChoice\NodeType;

use TheChoice\Contracts\OperatorInterface;
use TheChoice\Contracts\Sortable;

final class Rule implements Sortable
{
    private $_operator;
    private $_ruleType;
    private $_description = '';
    private $_priority;
    private $_params = [];

    public function __construct(OperatorInterface $operator, string $ruleType)
    {
        $this->_operator = $operator;
        $this->_ruleType = $ruleType;
    }

    public function setDescription(string $description)
    {
        $this->_description = $description;
        return $this;
    }

    public function setPriority(int $priority)
    {
        $this->_priority = $priority;
        return $this;
    }

    public function getOperator(): OperatorInterface
    {
        return $this->_operator;
    }

    public function getRuleType(): string
    {
        return $this->_ruleType;
    }

    public function getDescription(): string
    {
        return $this->_description;
    }

    public function getSortableValue()
    {
        return $this->_priority;
    }

    public function setParams(array $params)
    {
        $this->_params = $params;
    }

    public function getParams()
    {
        return $this->_params;
    }
}
