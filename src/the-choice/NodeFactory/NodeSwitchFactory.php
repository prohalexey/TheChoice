<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

use TheChoice\Builder\BuilderInterface;
use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Exception\LogicException;
use TheChoice\Node\SwitchCase;
use TheChoice\Node\SwitchNode;
use TheChoice\Operator\OperatorInterface;
use TheChoice\Operator\OperatorResolverInterface;

class NodeSwitchFactory implements NodeFactoryInterface
{
    private const string DEFAULT_OPERATOR = 'equal';

    public function build(BuilderInterface $builder, array &$structure): SwitchNode
    {
        self::validate($structure);

        $contextName = $structure['context'];

        $cases = $this->buildCases($builder, $structure['cases']);

        $defaultNode = null;
        if (array_key_exists('default', $structure)) {
            $defaultStructure = $structure['default'];
            if (!is_array($defaultStructure)) {
                throw new InvalidArgumentException('The "default" property must be an array');
            }

            $defaultNode = $builder->build($defaultStructure);
        }

        $node = new SwitchNode($contextName, $cases, $defaultNode);
        $node->setRoot($builder->getRoot());

        return $node;
    }

    /**
     * @param array<mixed> $casesData
     *
     * @return array<SwitchCase>
     */
    private function buildCases(BuilderInterface $builder, array $casesData): array
    {
        $cases = [];

        foreach ($casesData as $index => $caseData) {
            if (!is_array($caseData)) {
                throw new InvalidArgumentException(
                    sprintf('Case at index %d must be an array', $index),
                );
            }

            if (!array_key_exists('then', $caseData)) {
                throw new LogicException(
                    sprintf('The "then" property is absent in switch case at index %d', $index),
                );
            }

            $operatorName = array_key_exists('operator', $caseData) && is_string($caseData['operator'])
                ? $caseData['operator']
                : self::DEFAULT_OPERATOR;

            /** @var OperatorResolverInterface $operatorResolver */
            $operatorResolver = $builder->getContainer()->get(OperatorResolverInterface::class);
            $operatorClass = $operatorResolver->resolve($operatorName);

            $operator = $builder->getContainer()->get($operatorClass);
            if (!$operator instanceof OperatorInterface) {
                throw new InvalidArgumentException(
                    sprintf('Operator "%s" must implement OperatorInterface', $operatorName),
                );
            }

            if (array_key_exists('value', $caseData)) {
                $operator->setValue($caseData['value']);
            }

            $thenStructure = $caseData['then'];
            if (!is_array($thenStructure)) {
                throw new InvalidArgumentException(
                    sprintf('The "then" property at case index %d must be an array', $index),
                );
            }

            $thenNode = $builder->build($thenStructure);

            $cases[] = new SwitchCase($operator, $thenNode);
        }

        return $cases;
    }

    private static function validate(array $structure): void
    {
        foreach (['context', 'cases'] as $key) {
            if (!array_key_exists($key, $structure)) {
                throw new LogicException(
                    sprintf('The "%s" property is absent in node type "Switch"', $key),
                );
            }
        }

        if (!is_string($structure['context']) || '' === $structure['context']) {
            throw new InvalidArgumentException('The "context" property must be a non-empty string');
        }

        if (!is_array($structure['cases'])) {
            throw new InvalidArgumentException('The "cases" property must be an array');
        }
    }
}
