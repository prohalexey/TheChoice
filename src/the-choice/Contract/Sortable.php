<?php

namespace TheChoice\Contract;

interface Sortable
{
    /**
     * @return int|null
     */
    public function getSortableValue();
}