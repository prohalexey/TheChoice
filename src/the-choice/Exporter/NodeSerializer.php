<?php

declare(strict_types=1);

namespace TheChoice\Exporter;

use TheChoice\Exception\InvalidArgumentException;
use TheChoice\Node\AbstractNode;
use TheChoice\Node\Collection;
use TheChoice\Node\Condition;
use TheChoice\Node\Context;
use TheChoice\Node\Node;
use TheChoice\Node\Root;
use TheChoice\Node\SwitchNode;
use TheChoice\Node\Value;

/**
 * Converts a Node tree into a plain PHP array that mirrors the format
 * accepted by JsonBuilder / YamlBuilder.
 *
 * The output is designed to be round-trip safe:
 *   parse(json_encode(serialize(parse($json)))) produces identical behaviour.
 */
final class NodeSerializer
{
    /**
     * @return array<mixed>
     */
    public function toArray(Node $node): array
    {
        return match (true) {
            $node instanceof Root       => $this->serializeRoot($node),
            $node instanceof Condition  => $this->serializeCondition($node),
            $node instanceof Collection => $this->serializeCollection($node),
            $node instanceof SwitchNode => $this->serializeSwitch($node),
            $node instanceof Context    => $this->serializeContext($node),
            $node instanceof Value      => $this->serializeValue($node),
            default                     => throw new InvalidArgumentException(
                sprintf('Unsupported node type "%s"', $node::class),
            ),
        };
    }

    /**
     * @return array<mixed>
     */
    private function serializeRoot(Root $node): array
    {
        $data = ['node' => Root::getNodeName()];

        $this->addDescription($node, $data);

        $storage = $node->getStorage();
        if ([] !== $storage) {
            $data['storage'] = $storage;
        }

        $data['rules'] = $this->toArray($node->getRules());

        return $data;
    }

    /**
     * @return array<mixed>
     */
    private function serializeValue(Value $node): array
    {
        $data = ['node' => Value::getNodeName(), 'value' => $node->getValue()];

        $this->addDescription($node, $data);

        return $data;
    }

    /**
     * @return array<mixed>
     */
    private function serializeContext(Context $node): array
    {
        $data = ['node' => Context::getNodeName()];

        $this->addDescription($node, $data);

        $data['context'] = $node->getContextName();

        $operator = $node->getOperator();
        if (null !== $operator) {
            $data['operator'] = $operator::getOperatorName();
            // Operators like isEmpty / isNull do not use a comparison value
            if (null !== $operator->getValue()) {
                $data['value'] = $operator->getValue();
            }
        }

        $params = $node->getParams();
        if ([] !== $params) {
            $data['params'] = $params;
        }

        $modifiers = $node->getModifiers();
        if ([] !== $modifiers) {
            $data['modifiers'] = $modifiers;
        }

        $priority = $node->getSortableValue();
        if (0 !== $priority) {
            $data['priority'] = $priority;
        }

        if ($node->isStoppable()) {
            $data['break'] = $node->getStoppableType();
        }

        return $data;
    }

    /**
     * @return array<mixed>
     */
    private function serializeCondition(Condition $node): array
    {
        $data = ['node' => Condition::getNodeName()];

        $this->addDescription($node, $data);

        $priority = $node->getSortableValue();
        if (0 !== $priority) {
            $data['priority'] = $priority;
        }

        $data['if'] = $this->toArray($node->getIfNode());
        $data['then'] = $this->toArray($node->getThenNode());

        $elseNode = $node->getElseNode();
        if (null !== $elseNode) {
            $data['else'] = $this->toArray($elseNode);
        }

        return $data;
    }

    /**
     * @return array<mixed>
     */
    private function serializeCollection(Collection $node): array
    {
        $data = ['node' => Collection::getNodeName(), 'type' => $node->getType()];

        $this->addDescription($node, $data);

        $count = $node->getCount();
        if (null !== $count) {
            $data['count'] = $count;
        }

        $priority = $node->getSortableValue();
        if (0 !== $priority) {
            $data['priority'] = $priority;
        }

        $data['nodes'] = array_map(
            fn (Node $child): array => $this->toArray($child),
            $node->all(),
        );

        return $data;
    }

    /**
     * @return array<mixed>
     */
    private function serializeSwitch(SwitchNode $node): array
    {
        $data = ['node' => SwitchNode::getNodeName(), 'context' => $node->getContextName()];

        $this->addDescription($node, $data);

        $cases = [];
        foreach ($node->getCases() as $case) {
            $operator = $case->getOperator();
            $caseEntry = ['operator' => $operator::getOperatorName()];

            if (null !== $operator->getValue()) {
                $caseEntry['value'] = $operator->getValue();
            }

            $caseEntry['then'] = $this->toArray($case->getThenNode());

            $cases[] = $caseEntry;
        }

        $data['cases'] = $cases;

        $defaultNode = $node->getDefaultNode();
        if (null !== $defaultNode) {
            $data['default'] = $this->toArray($defaultNode);
        }

        return $data;
    }

    /**
     * @param array<mixed> $data
     */
    private function addDescription(AbstractNode $node, array &$data): void
    {
        $description = $node->getDescription();
        if ('' !== $description) {
            $data['description'] = $description;
        }
    }
}
