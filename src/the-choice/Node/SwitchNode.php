<?php

declare(strict_types=1);

namespace TheChoice\Node;

class SwitchNode extends AbstractChildNode
{
    /**
     * @param array<SwitchCase> $cases
     */
    public function __construct(
        private readonly string $contextName,
        private readonly array $cases,
        private readonly ?Node $defaultNode = null,
    ) {
    }

    public static function getNodeName(): string
    {
        return 'switch';
    }

    public function getContextName(): string
    {
        return $this->contextName;
    }

    /**
     * @return array<SwitchCase>
     */
    public function getCases(): array
    {
        return $this->cases;
    }

    public function getDefaultNode(): ?Node
    {
        return $this->defaultNode;
    }
}
