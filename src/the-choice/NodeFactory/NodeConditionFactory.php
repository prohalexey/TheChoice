<?php

declare(strict_types=1);

namespace TheChoice\NodeFactory;

use InvalidArgumentException;
use TheChoice\Builder\BuilderInterface;
use TheChoice\Exception\LogicException;
use TheChoice\Node\Condition;

class NodeConditionFactory implements NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure): Condition
    {
        self::validate($structure);

        $ifStructure = $structure['if'];
        $thenStructure = $structure['then'];
        $elseStructure = $structure['else'] ?? null;

        if (!is_array($ifStructure) || !is_array($thenStructure) || (null !== $elseStructure && !is_array($elseStructure))) {
            throw new InvalidArgumentException('Node structures must be arrays');
        }

        $ifNode = $builder->build($ifStructure);
        $thenNode = $builder->build($thenStructure);
        $elseNode = null !== $elseStructure ? $builder->build($elseStructure) : null;

        $node = new Condition($ifNode, $thenNode, $elseNode);
        $node->setRoot($builder->getRoot());

        if (array_key_exists('description', $structure)) {
            $description = $structure['description'];
            if (is_string($description)) {
                $node->setDescription($description);
            }
        }

        if (array_key_exists('priority', $structure)) {
            $priority = $structure['priority'];
            if (is_numeric($priority)) {
                $node->setPriority((int)$priority);
            }
        }

        return $node;
    }

    private static function validate(array $structure): void
    {
        foreach (['if', 'then'] as $key) {
            if (!array_key_exists($key, $structure)) {
                throw new LogicException(sprintf('The "%s" property is absent in node type "Condition"!', $key));
            }
        }
    }
}
