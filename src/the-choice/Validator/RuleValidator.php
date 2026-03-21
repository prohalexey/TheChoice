<?php

declare(strict_types=1);

namespace TheChoice\Validator;

use TheChoice\Exception\ValidationException;
use TheChoice\Node\Collection;
use TheChoice\Node\Condition;
use TheChoice\Node\Context;
use TheChoice\Node\Node;
use TheChoice\Node\Root;
use TheChoice\Node\SwitchNode;
use TheChoice\Node\Value;

/**
 * Validates a rule node tree, checking that all referenced contexts and operators
 * are present in the provided allowed lists.
 *
 * Does NOT execute the tree — this is a static (structural) check.
 */
final readonly class RuleValidator
{
    private const int LEVENSHTEIN_THRESHOLD = 3;

    /** @var array<string> */
    private array $contexts;

    /** @var array<string> */
    private array $operators;

    /**
     * @param array<string> $contexts  list of allowed context names
     * @param array<string> $operators list of allowed operator names
     */
    public function __construct(array $contexts, array $operators)
    {
        $this->contexts = array_values($contexts);
        $this->operators = array_values($operators);
    }

    /**
     * Validates the given node tree and returns a ValidationResult.
     */
    public function validate(Node $node): ValidationResult
    {
        $errors = [];
        $this->walkNode($node, 'root', $errors);

        return new ValidationResult($errors);
    }

    /**
     * Validates the given node tree and throws ValidationException if there are errors.
     *
     * @throws ValidationException
     */
    public function validateOrThrow(Node $node): void
    {
        $result = $this->validate($node);
        if (!$result->isValid()) {
            throw new ValidationException($result);
        }
    }

    /**
     * @param array<ValidationError> $errors collected errors (passed by reference)
     */
    private function walkNode(Node $node, string $path, array &$errors): void
    {
        if ($node instanceof Root) {
            $this->walkNode($node->getRules(), $path . ' > rules', $errors);

            return;
        }

        if ($node instanceof Condition) {
            $this->walkNode($node->getIfNode(), $path . ' > condition.if', $errors);
            $this->walkNode($node->getThenNode(), $path . ' > condition.then', $errors);

            $elseNode = $node->getElseNode();
            if (null !== $elseNode) {
                $this->walkNode($elseNode, $path . ' > condition.else', $errors);
            }

            return;
        }

        if ($node instanceof Collection) {
            foreach ($node->all() as $index => $childNode) {
                $this->walkNode($childNode, sprintf('%s > collection[%d]', $path, $index), $errors);
            }

            return;
        }

        if ($node instanceof Context) {
            $this->validateContext($node, $path, $errors);

            return;
        }

        if ($node instanceof SwitchNode) {
            $this->validateSwitchNode($node, $path, $errors);

            return;
        }

        // Value nodes and other leaf nodes require no validation
    }

    /**
     * @param array<ValidationError> $errors
     */
    private function validateSwitchNode(SwitchNode $node, string $path, array &$errors): void
    {
        $contextName = $node->getContextName();
        if ([] !== $this->contexts && !in_array($contextName, $this->contexts, true)) {
            $suggestion = $this->findClosestMatch($contextName, $this->contexts);

            $errors[] = new ValidationError(
                message: sprintf('Context "%s" is not registered', $contextName),
                path: $path,
                suggestion: $suggestion,
            );
        }

        foreach ($node->getCases() as $index => $case) {
            $casePath = sprintf('%s > switch.cases[%d]', $path, $index);

            if ([] !== $this->operators) {
                $operatorName = $case->getOperator()::getOperatorName();
                if (!in_array($operatorName, $this->operators, true)) {
                    $suggestion = $this->findClosestMatch($operatorName, $this->operators);

                    $errors[] = new ValidationError(
                        message: sprintf('Operator "%s" is not registered', $operatorName),
                        path: $casePath,
                        suggestion: $suggestion,
                    );
                }
            }

            $this->walkNode($case->getThenNode(), $casePath . ' > then', $errors);
        }

        $defaultNode = $node->getDefaultNode();
        if (null !== $defaultNode) {
            $this->walkNode($defaultNode, $path . ' > switch.default', $errors);
        }
    }

    /**
     * @param array<ValidationError> $errors
     */
    private function validateContext(Context $node, string $path, array &$errors): void
    {
        $contextName = $node->getContextName();
        if (null !== $contextName && [] !== $this->contexts && !in_array($contextName, $this->contexts, true)) {
            $suggestion = $this->findClosestMatch($contextName, $this->contexts);

            $errors[] = new ValidationError(
                message: sprintf('Context "%s" is not registered', $contextName),
                path: $path,
                suggestion: $suggestion,
            );
        }

        $operator = $node->getOperator();
        if (null !== $operator && [] !== $this->operators) {
            $operatorName = $operator::getOperatorName();
            if (!in_array($operatorName, $this->operators, true)) {
                $suggestion = $this->findClosestMatch($operatorName, $this->operators);

                $errors[] = new ValidationError(
                    message: sprintf('Operator "%s" is not registered', $operatorName),
                    path: $path,
                    suggestion: $suggestion,
                );
            }
        }
    }

    /**
     * Finds the closest matching string from the allowed list using Levenshtein distance.
     *
     * @param array<string> $allowed
     */
    private function findClosestMatch(string $needle, array $allowed): ?string
    {
        $bestMatch = null;
        $bestDistance = self::LEVENSHTEIN_THRESHOLD + 1;

        foreach ($allowed as $candidate) {
            $distance = levenshtein($needle, $candidate);
            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestMatch = $candidate;
            }
        }

        return $bestMatch;
    }
}
