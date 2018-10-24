<?php

namespace TheChoice;

use TheChoice\Contracts\ContextFactoryInterface;

class RuleChecker
{
    private $_contextFactory = [];

    public function __construct(ContextFactoryInterface $contextFactory)
    {
        $this->_contextFactory = $contextFactory;
    }

    public function assert(Collection $collection): bool
    {
        $collectionType = $collection->getType();

        if ($collectionType === Collection::TYPE_AND) {
            $fullResult = true;
        } elseif ($collectionType === Collection::TYPE_OR) {
            $fullResult = false;
        } else {
            throw new \InvalidArgumentException(sprintf('Unknown collection type "%s"', $collection->getType()));
        }

        foreach ($collection->all() as $item) {
            if ($item instanceof Collection) {
                $result = $this->assert($item);
            } elseif ($item instanceof Rule) {
                $context = $this->_contextFactory->createContextFromRule($item);
                $result = $item->getOperator()->assert($context);
            } else {
                throw new \InvalidArgumentException(sprintf('Unsupported type "%s"', $item));
            }

            if ($collectionType === Collection::TYPE_AND) {
                if ($result === false) {
                    return false;
                }
            }

            if ($collectionType === Collection::TYPE_OR) {
                if ($result === true) {
                    return true;
                }
            }
        }

        return $fullResult;
    }
}