<?php

namespace TheChoice;

use TheChoice\Contracts\OperatorInterface;

class Rule
{
    private $_operator;
    private $_ruleType = '';
    private $_description = '';

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
}
