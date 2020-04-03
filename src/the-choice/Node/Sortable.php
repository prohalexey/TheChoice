<?php

declare(strict_types=1);

namespace TheChoice\Node;

interface Sortable
{
    /**
     * @return int|null
     */
    public function getSortableValue();
}