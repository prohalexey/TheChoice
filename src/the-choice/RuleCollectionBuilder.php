<?php

namespace TheChoice;

use TheChoice\Contracts\OperatorFactoryInterface;
use TheChoice\Contracts\RuleCollectionBuilderInterface;

final class RuleCollectionBuilder implements RuleCollectionBuilderInterface
{
    private $_operatorFactory;

    public function __construct(OperatorFactoryInterface $operatorFactory)
    {
        $this->_operatorFactory = $operatorFactory;
    }

    public function build($structure): Collection
    {
        $item = $this->recursiveBuild($structure);

        if ($item instanceof Rule) {
            $collection = new Collection(Collection::TYPE_AND);
            $collection->add($item);

            return $collection;
        }

        return $item;
    }

    private function recursiveBuild($structure)
    {
        if (\is_array($structure) && isset($structure['rules']) && \is_array($structure['rules']) && \count($structure['rules']) > 0) {
            return $this->buildRules($structure);
        }

        $this->validateRuleParams($structure);
        return $this->buildRule($structure);
    }

    private function validateRuleParams($structure)
    {
        $keysThatMustBePresent = [
            'rule',
            'operator',
            'value',
        ];

        foreach ($keysThatMustBePresent as $key) {
            if (!array_key_exists($key, $structure)) {
                throw new \LogicException(sprintf('The "%s" property is absent!', $key));
            }
        }
    }

    private function buildRule($structure): Rule
    {
        $operatorInstance = $this->_operatorFactory->create(
            $structure['operator'],
            $structure['value']
        );

        $rule = new Rule($operatorInstance, $structure['rule']);

        if (array_key_exists('description', $structure)) {
            $rule->setDescription($structure['description']);
        }

        return $rule;
    }

    private function buildRules($structure): Collection
    {
        $orCollection = new Collection(Collection::TYPE_OR);
        foreach ($structure['rules'] as $orRules) {
            $andRuleBag = new Collection(Collection::TYPE_AND);
            $orCollection->add($andRuleBag);
            foreach ($orRules as $andRules) {
                $andRuleBag->add($this->recursiveBuild($andRules));
            }
        }

        return $orCollection;
    }
}
