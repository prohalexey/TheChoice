<?php

declare(strict_types=1);

namespace TheChoice\Node;

interface Sortable
{
    public function getSortableValue(): ?int;
}
