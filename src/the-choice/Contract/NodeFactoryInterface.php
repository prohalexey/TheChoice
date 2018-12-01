<?php

namespace TheChoice\Contract;

interface NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure);
}