<?php

declare(strict_types=1);

namespace TheChoice\Processor;

use InvalidArgumentException;
use Override;
use TheChoice\Context\ContextFactoryInterface;
use TheChoice\Exception\RuntimeException;
use TheChoice\Node\Context;
use TheChoice\Node\Node;
use TheChoice\Node\SwitchNode;

class SwitchProcessor extends AbstractProcessor
{
    protected ?ContextFactoryInterface $contextFactory = null;

    public function setContextFactory(ContextFactoryInterface $contextFactory): self
    {
        $this->contextFactory = $contextFactory;

        return $this;
    }

    #[Override]
    public function process(Node $node): mixed
    {
        if (!$node instanceof SwitchNode) {
            throw new InvalidArgumentException('Node must be an instance of SwitchNode');
        }

        if (null === $this->contextFactory) {
            throw new RuntimeException('Context factory not configured');
        }

        $contextName = $node->getContextName();

        $this->traceCollector?->begin('Switch', $contextName);

        // Resolve context value once for all cases
        $contextNode = new Context();
        $contextNode->setContextName($contextName);
        $contextNode->setRoot($node->getRoot());

        $contextInterface = $this->contextFactory->createContextFromContextNode($contextNode);

        foreach ($node->getCases() as $case) {
            if ($case->getOperator()->assert($contextInterface)) {
                $thenNode = $case->getThenNode();
                $thenProcessor = $this->getProcessorByNode($thenNode);
                $result = null !== $thenProcessor ? $thenProcessor->process($thenNode) : null;

                $this->traceCollector?->end($result);

                return $result;
            }
        }

        $defaultNode = $node->getDefaultNode();
        if (null !== $defaultNode) {
            $defaultProcessor = $this->getProcessorByNode($defaultNode);
            $result = null !== $defaultProcessor ? $defaultProcessor->process($defaultNode) : null;

            $this->traceCollector?->end($result);

            return $result;
        }

        $this->traceCollector?->end(null);

        return null;
    }
}
