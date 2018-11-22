<?php

namespace TheChoice\Contracts;

interface NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure);
}