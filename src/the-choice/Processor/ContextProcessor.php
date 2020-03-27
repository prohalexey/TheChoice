<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use ChrisKonnertz\StringCalc\StringCalc;

use Exception;
use TheChoice\Exception\InvalidContextCalculation;
use TheChoice\Exception\RuntimeException;
use TheChoice\Context\CallableContext;
use TheChoice\Context\ContextFactoryInterface;
use TheChoice\Node\Context;
use TheChoice\Operator\OperatorInterface;

class ContextProcessor extends AbstractProcessor
{
    /** @var ContextFactoryInterface */
    protected $contextFactory;

    protected $processedContext = [];

    public function setContextFactory(ContextFactoryInterface $contextFactory): self
    {
        $this->contextFactory = $contextFactory;
        return $this;
    }

    public function process(Context $node)
    {
        if (null === $this->contextFactory) {
            throw new RuntimeException('Context factory not configured');
        }

        $hash = [
            $node->getContextName(),
        ];

        $params = $node->getParams();
        if (count($params) > 0) {
            $hash[] = hash('md5', serialize($params));
        }

        $operator = $node->getOperator();
        if (null !== $operator) {
            /** @var OperatorInterface $operator */
            $operatorValue = $operator->getValue();

            $hash[] = get_class($operator);
            $hash[] = is_array($operatorValue) || is_object($operatorValue)
                ? hash('md5', serialize($operatorValue))
                : $operatorValue;
        }

        $modifiers = $node->getModifiers();
        if (count($modifiers) > 0) {
            $hash[] = hash('md5', serialize($modifiers));
        }

        $hash = implode('', $hash);

        if (!isset($this->processedContext[$hash])) {
            $context = $this->contextFactory->createContextFromContextNode($node);

            if (null !== $operator) {
                if (count($modifiers) > 0) {
                    $context = new CallableContext(function () use ($context, $node) {
                        return $this->processContextModifiers($context->getValue(), $node);
                    });
                }

                $this->processedContext[$hash] = $operator->assert($context);
            } else {
                $this->processedContext[$hash] = $this->processContextModifiers($context->getValue(), $node);
            }
        }

        if ($node->isStoppable()) {
            $node->getRoot()->setResult($this->processedContext[$hash]);

            /**
             * If the "Root" node has a result, we should stop here.
             * It does not matter what we return, the result is already set to the "Root" node
             */
            if ($node->getStoppableType() === Context::STOP_IMMEDIATELY) {
                return;
            }
        }

        return $this->processedContext[$hash];
    }

    private function processContextModifiers($value, Context $node)
    {
        $vars = ['$context' => $value];

        $storage = $node->getRoot()->getStorage();
        $vars = array_merge($vars, $storage);

        foreach ($node->getModifiers() as $modifier) {
            $modifier = str_replace(array_keys($vars), array_values($vars), $modifier);

            try {
                $value = (new StringCalc())->calculate($modifier);
            } catch (Exception $exception) {
                throw new InvalidContextCalculation($exception->getMessage());
            }

            $vars['$context'] = $value;
        }

        return $value;
    }
}