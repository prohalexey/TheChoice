<?php

namespace TheChoice\Contracts;

use TheChoice\Collection;

interface RuleCollectionBuilderInterface
{
    public function build($structure): Collection;
}