<?php

namespace TheChoice\Factory;

use TheChoice\Contracts\RuleContextFactoryInterface;
use TheChoice\Contracts\RuleContextInterface;
use TheChoice\NodeType\Rule;

class RuleContextFactory extends AbstractContextFactory implements RuleContextFactoryInterface
{
    public function createContextFromRuleNode(Rule $rule): RuleContextInterface
    {
        $ruleType = $rule->getRuleType();

        $context = $this->getContext($ruleType);
        $context = $this->setParamsToObject($context, $rule->getParams());

        return $context;
    }

    protected function checkType($context)
    {
        if (!$context instanceof RuleContextInterface) {
            throw new \InvalidArgumentException(
                sprintf('Object "%s" not implements ActionContextInterface', \get_class($context))
            );
        }
    }
}