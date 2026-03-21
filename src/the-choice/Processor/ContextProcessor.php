<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use ChrisKonnertz\StringCalc\StringCalc;
use InvalidArgumentException;
use TheChoice\Context\CallableContext;
use TheChoice\Context\ContextFactoryInterface;
use TheChoice\Exception\InvalidContextCalculation;
use TheChoice\Exception\RuntimeException;
use TheChoice\Node\Context;
use TheChoice\Node\Node;
use TheChoice\Operator\OperatorInterface;
use Throwable;

class ContextProcessor extends AbstractProcessor
{
    protected ?ContextFactoryInterface $contextFactory = null;

    protected array $processedContext = [];

    public function setContextFactory(ContextFactoryInterface $contextFactory): self
    {
        $this->contextFactory = $contextFactory;

        return $this;
    }

    public function flush(): void
    {
        $this->processedContext = [];
    }

    public function process(Node $node): mixed
    {
        if (!$node instanceof Context) {
            throw new InvalidArgumentException('Node must be an instance of Context');
        }

        if (null === $this->contextFactory) {
            throw new RuntimeException('Context factory not configured');
        }

        $hash = [
            $node->getContextName(),
        ];

        $params = $node->getParams();
        if ([] !== $params) {
            $hash[] = hash('md5', serialize($params));
        }

        $operator = $node->getOperator();
        if (null !== $operator) {
            /** @var OperatorInterface $operator */
            $operatorValue = $operator->getValue();

            $hash[] = $operator::class;
            if (is_array($operatorValue) || is_object($operatorValue)) {
                $hash[] = hash('md5', serialize($operatorValue));
            } elseif (null !== $operatorValue) {
                $hash[] = $operatorValue;
            }
        }

        $modifiers = $node->getModifiers();
        if ([] !== $modifiers) {
            $hash[] = hash('md5', serialize($modifiers));
        }

        $hash = implode('', $hash);

        if (!isset($this->processedContext[$hash])) {
            $context = $this->contextFactory->createContextFromContextNode($node);

            if (null !== $operator) {
                if ([] !== $modifiers) {
                    $context = new CallableContext(
                        fn (): mixed => $this->processContextModifiers($context->getValue(), $node),
                    );
                }

                $this->processedContext[$hash] = $operator->assert($context);
            } else {
                $this->processedContext[$hash] = $this->processContextModifiers($context->getValue(), $node);
            }
        }

        if ($node->isStoppable()) {
            $node->getRoot()->setResult($this->processedContext[$hash]);

            /*
             * If the "Root" node has a result, we should stop here.
             * It does not matter what we return; the result is already set to the "Root" node
             */
            if (Context::STOP_IMMEDIATELY === $node->getStoppableType()) {
                return null;
            }
        }

        return $this->processedContext[$hash];
    }

    private function processContextModifiers(mixed $value, Context $node): mixed
    {
        $vars = ['$context' => $value];

        $storage = $node->getRoot()->getStorage();
        $vars = array_merge($vars, $storage);

        foreach ($node->getModifiers() as $modifier) {
            $search = array_keys($vars);
            $replace = array_values($vars);
            /** @var array<string> $search */
            /** @var array<string> $replace */
            $modifier = str_replace($search, $replace, $modifier);

            try {
                $value = (new StringCalc())->calculate($modifier);
            } catch (Throwable $throwable) {
                throw new InvalidContextCalculation($throwable->getMessage());
            }

            $vars['$context'] = $value;
        }

        return $value;
    }
}
