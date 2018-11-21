<?php

namespace TheChoice\Factory;

use TheChoice\Contracts\ActionContextFactoryInterface;
use TheChoice\Contracts\ActionContextInterface;
use TheChoice\NodeType\Action;

class ActionContextFactory extends AbstractContextFactory implements ActionContextFactoryInterface
{
    public function createContextFromActionNode(Action $action): ActionContextInterface
    {
        $actionType = $action->getAction();

        $context = $this->getContext($actionType);
        $context = $this->setParamsToObject($context, $action->getParams());

        return $context;
    }

    protected function checkType($context)
    {
        if (!$context instanceof ActionContextInterface) {
            throw new \InvalidArgumentException(
                sprintf('Object "%s" not implements ActionContextInterface', \get_class($context))
            );
        }
    }
}