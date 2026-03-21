<?php

declare(strict_types=1);

namespace TheChoice\Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use TheChoice\Context\ContextInterface;
use TheChoice\Exception\ValidationException;
use TheChoice\Node\AbstractChildNode;
use TheChoice\Node\Collection;
use TheChoice\Node\Condition;
use TheChoice\Node\Context;
use TheChoice\Node\Node;
use TheChoice\Node\Root;
use TheChoice\Node\Value;
use TheChoice\Operator\AbstractOperator;
use TheChoice\Operator\ArrayContain;
use TheChoice\Operator\Equal;
use TheChoice\Operator\GreaterThan;
use TheChoice\Operator\OperatorInterface;
use TheChoice\Validator\RuleValidator;

final class RuleValidatorTest extends TestCase
{
    private const array CONTEXTS = ['withdrawalCount', 'inGroup', 'getDepositSum', 'depositCount'];

    private const array OPERATORS = ['equal', 'arrayContain', 'greaterThan'];

    private RuleValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new RuleValidator(
            contexts: self::CONTEXTS,
            operators: self::OPERATORS,
        );
    }

    public function testValidNodeReturnsNoErrors(): void
    {
        $root = $this->buildRoot(
            $this->buildContext('withdrawalCount', new Equal()),
        );

        $result = $this->validator->validate($root);

        self::assertTrue($result->isValid());
        self::assertSame([], $result->getErrors());
    }

    public function testUnknownContextIsReported(): void
    {
        $root = $this->buildRoot(
            $this->buildContext('unknownContext', new Equal()),
        );

        $result = $this->validator->validate($root);

        self::assertFalse($result->isValid());
        self::assertCount(1, $result->getErrors());
        self::assertStringContainsString('Context "unknownContext" is not registered', $result->getErrors()[0]->message);
    }

    public function testUnknownOperatorIsReported(): void
    {
        $context = new Context();
        $context->setContextName('withdrawalCount');

        $operatorMock = new class extends AbstractOperator {
            public static function getOperatorName(): string
            {
                return 'unknownOperator';
            }

            public function assert(ContextInterface $context): bool
            {
                return false;
            }
        };

        $context->setOperator($operatorMock);
        $root = $this->buildRoot($context);

        $result = $this->validator->validate($root);

        self::assertFalse($result->isValid());
        self::assertCount(1, $result->getErrors());
        self::assertStringContainsString('Operator "unknownOperator" is not registered', $result->getErrors()[0]->message);
    }

    public function testDidYouMeanSuggestionForContext(): void
    {
        $root = $this->buildRoot(
            $this->buildContext('withdrawlCount', new Equal()),
        );

        $result = $this->validator->validate($root);

        self::assertFalse($result->isValid());
        self::assertSame('withdrawalCount', $result->getErrors()[0]->suggestion);
    }

    public function testDidYouMeanSuggestionForOperator(): void
    {
        $context = new Context();
        $context->setContextName('withdrawalCount');

        $operatorMock = new class extends AbstractOperator {
            public static function getOperatorName(): string
            {
                return 'equl';
            }

            public function assert(ContextInterface $context): bool
            {
                return false;
            }
        };

        $context->setOperator($operatorMock);
        $root = $this->buildRoot($context);

        $result = $this->validator->validate($root);

        self::assertFalse($result->isValid());
        self::assertSame('equal', $result->getErrors()[0]->suggestion);
    }

    public function testNoSuggestionWhenDistanceIsTooLarge(): void
    {
        $root = $this->buildRoot(
            $this->buildContext('zzzzzzzzzzz', new Equal()),
        );

        $result = $this->validator->validate($root);

        self::assertFalse($result->isValid());
        self::assertNull($result->getErrors()[0]->suggestion);
    }

    public function testValidationTraversesNestedConditions(): void
    {
        $ifContext = $this->buildContext('withdrawalCount', new Equal());
        $thenContext = $this->buildContext('unknownThen', new GreaterThan());
        $elseValue = new Value(5);

        $condition = new Condition($ifContext, $thenContext, $elseValue);

        $root = $this->buildRoot($condition);

        $result = $this->validator->validate($root);

        self::assertFalse($result->isValid());
        self::assertCount(1, $result->getErrors());
        self::assertStringContainsString('unknownThen', $result->getErrors()[0]->message);
    }

    public function testValidationTraversesCollections(): void
    {
        $context1 = $this->buildContext('unknownA', new Equal());
        $context2 = $this->buildContext('unknownB', new ArrayContain());

        $collection = new Collection(Collection::TYPE_AND);
        $collection->add($context1);
        $collection->add($context2);

        $root = $this->buildRoot($collection);

        $result = $this->validator->validate($root);

        self::assertFalse($result->isValid());
        self::assertCount(2, $result->getErrors());
    }

    public function testValueNodesAreSkippedWithoutErrors(): void
    {
        $root = $this->buildRoot(new Value(42));

        $result = $this->validator->validate($root);

        self::assertTrue($result->isValid());
    }

    public function testContextWithoutOperatorIsValidated(): void
    {
        $context = new Context();
        $context->setContextName('getDepositSum');

        $root = $this->buildRoot($context);

        $result = $this->validator->validate($root);

        self::assertTrue($result->isValid());
    }

    public function testMultipleErrorsCollected(): void
    {
        $context1 = $this->buildContext('badContext1', new Equal());
        $context2 = $this->buildContext('badContext2', new GreaterThan());

        $collection = new Collection(Collection::TYPE_OR);
        $collection->add($context1);
        $collection->add($context2);

        $root = $this->buildRoot($collection);

        $result = $this->validator->validate($root);

        self::assertFalse($result->isValid());
        self::assertCount(2, $result->getErrors());
    }

    public function testValidateOrThrowDoesNotThrowOnValidNode(): void
    {
        $root = $this->buildRoot(
            $this->buildContext('withdrawalCount', new Equal()),
        );

        $this->validator->validateOrThrow($root);
        $this->addToAssertionCount(1);
    }

    public function testValidateOrThrowThrowsOnInvalidNode(): void
    {
        $root = $this->buildRoot(
            $this->buildContext('unknownContext', new Equal()),
        );

        $this->expectException(ValidationException::class);
        $this->validator->validateOrThrow($root);
    }

    public function testValidationExceptionContainsResult(): void
    {
        $root = $this->buildRoot(
            $this->buildContext('unknownContext', new Equal()),
        );

        try {
            $this->validator->validateOrThrow($root);
            self::fail('Expected ValidationException was not thrown');
        } catch (ValidationException $exception) {
            $result = $exception->getValidationResult();
            self::assertFalse($result->isValid());
            self::assertCount(1, $result->getErrors());
        }
    }

    public function testToStringOutputForErrors(): void
    {
        $root = $this->buildRoot(
            $this->buildContext('withdrawlCount', new Equal()),
        );

        $result = $this->validator->validate($root);

        $output = $result->toString();
        self::assertStringContainsString('withdrawlCount', $output);
        self::assertStringContainsString('did you mean "withdrawalCount"', $output);
    }

    public function testToStringOutputForValidResult(): void
    {
        $root = $this->buildRoot(
            $this->buildContext('withdrawalCount', new Equal()),
        );

        $result = $this->validator->validate($root);

        self::assertSame('Validation passed: no errors found.', $result->toString());
    }

    public function testEmptyContextListSkipsContextValidation(): void
    {
        $validator = new RuleValidator(contexts: [], operators: self::OPERATORS);

        $root = $this->buildRoot(
            $this->buildContext('anyContext', new Equal()),
        );

        $result = $validator->validate($root);

        self::assertTrue($result->isValid());
    }

    public function testEmptyOperatorListSkipsOperatorValidation(): void
    {
        $validator = new RuleValidator(contexts: self::CONTEXTS, operators: []);

        $operatorMock = new class extends AbstractOperator {
            public static function getOperatorName(): string
            {
                return 'customOperator';
            }

            public function assert(ContextInterface $context): bool
            {
                return false;
            }
        };

        $context = new Context();
        $context->setContextName('withdrawalCount');
        $context->setOperator($operatorMock);

        $root = $this->buildRoot($context);

        $result = $validator->validate($root);

        self::assertTrue($result->isValid());
    }

    public function testDeeplyNestedStructure(): void
    {
        $innerContext = $this->buildContext('badDeep', new Equal());
        $innerCollection = new Collection(Collection::TYPE_AND);
        $innerCollection->add($innerContext);

        $condition = new Condition(
            $innerCollection,
            new Value(true),
            new Value(false),
        );

        $outerCollection = new Collection(Collection::TYPE_OR);
        $outerCollection->add($condition);

        $root = $this->buildRoot($outerCollection);

        $result = $this->validator->validate($root);

        self::assertFalse($result->isValid());
        self::assertCount(1, $result->getErrors());
        self::assertStringContainsString('badDeep', $result->getErrors()[0]->message);
    }

    public function testPathIsIncludedInErrors(): void
    {
        $context = $this->buildContext('unknownCtx', new Equal());
        $collection = new Collection(Collection::TYPE_AND);
        $collection->add(new Value(1));
        $collection->add($context);

        $root = $this->buildRoot($collection);

        $result = $this->validator->validate($root);

        self::assertFalse($result->isValid());
        self::assertStringContainsString('collection[1]', $result->getErrors()[0]->path);
    }

    public function testBothUnknownContextAndUnknownOperatorOnSameNode(): void
    {
        $operatorMock = new class extends AbstractOperator {
            public static function getOperatorName(): string
            {
                return 'unknownOp';
            }

            public function assert(ContextInterface $context): bool
            {
                return false;
            }
        };

        $context = new Context();
        $context->setContextName('unknownCtx');
        $context->setOperator($operatorMock);

        $root = $this->buildRoot($context);

        $result = $this->validator->validate($root);

        self::assertFalse($result->isValid());
        self::assertCount(2, $result->getErrors());
        self::assertStringContainsString('Context "unknownCtx"', $result->getErrors()[0]->message);
        self::assertStringContainsString('Operator "unknownOp"', $result->getErrors()[1]->message);
    }

    public function testValidateNonRootNodeDirectly(): void
    {
        $context = $this->buildContext('unknownDirect', new Equal());

        $result = $this->validator->validate($context);

        self::assertFalse($result->isValid());
        self::assertCount(1, $result->getErrors());
        self::assertStringContainsString('unknownDirect', $result->getErrors()[0]->message);
    }

    public function testValidateConditionWithoutElse(): void
    {
        $ifContext = $this->buildContext('withdrawalCount', new Equal());
        $thenContext = $this->buildContext('unknownThen', new GreaterThan());

        $condition = new Condition($ifContext, $thenContext);
        $root = $this->buildRoot($condition);

        $result = $this->validator->validate($root);

        self::assertFalse($result->isValid());
        self::assertCount(1, $result->getErrors());
        self::assertStringContainsString('condition.then', $result->getErrors()[0]->path);
    }

    public function testValidateEmptyCollection(): void
    {
        $collection = new Collection(Collection::TYPE_AND);
        $root = $this->buildRoot($collection);

        $result = $this->validator->validate($root);

        self::assertTrue($result->isValid());
    }

    private function buildContext(string $name, ?OperatorInterface $operator = null): Context
    {
        $context = new Context();
        $context->setContextName($name);
        if (null !== $operator) {
            $context->setOperator($operator);
        }

        return $context;
    }

    private function buildRoot(mixed $childNode): Root
    {
        $root = new Root();
        $root->setRules($childNode);

        if ($childNode instanceof AbstractChildNode) {
            $this->setRootRecursively($childNode, $root);
        }

        return $root;
    }

    private function setRootRecursively(Node $node, Root $root): void
    {
        if ($node instanceof AbstractChildNode) {
            $node->setRoot($root);
        }

        if ($node instanceof Condition) {
            $this->setRootRecursively($node->getIfNode(), $root);
            $this->setRootRecursively($node->getThenNode(), $root);
            $elseNode = $node->getElseNode();
            if (null !== $elseNode) {
                $this->setRootRecursively($elseNode, $root);
            }
        }

        if ($node instanceof Collection) {
            foreach ($node->all() as $child) {
                $this->setRootRecursively($child, $root);
            }
        }
    }
}
