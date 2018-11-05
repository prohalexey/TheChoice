<?php

namespace TheChoice\Factory;

use TheChoice\Contracts\BuilderInterface;
use TheChoice\Contracts\OperatorFactoryInterface;
use TheChoice\NodeType\Rule;

class NodeRuleFactory
{
    private $_operatorFactory;

    public function __construct(OperatorFactoryInterface $operatorFactory)
    {
        $this->_operatorFactory = $operatorFactory;
    }

    public function build(BuilderInterface $builder, array &$structure): Rule
    {
        self::validate($structure);

        $operatorInstance = $this->_operatorFactory->create(
            $structure['operator'],
            $structure['value']
        );

        $node = new Rule($operatorInstance, $structure['rule']);

        if (self::nodeHasDescription($structure)) {
            $node->setDescription($structure['description']);
        }

        return $node;
    }

    private static function validate(array &$structure)
    {
        $keysThatMustBePresent = [
            'rule',
            'operator',
            'value',
        ];

        foreach ($keysThatMustBePresent as $key) {
            if (!array_key_exists($key, $structure)) {
                throw new \LogicException(sprintf('The "%s" property is absent in node type "rule"!', $key));
            }
        }
    }

    private static function nodeHasDescription(array &$structure): bool
    {
        return array_key_exists('description', $structure) && \is_string($structure['description']);
    }
}