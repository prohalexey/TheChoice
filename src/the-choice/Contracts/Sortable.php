<?php

namespace TheChoice\Contracts;

interface Sortable
{
    /**
     * @return int|null
     */
    public function getSortableValue();
}