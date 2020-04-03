<?php

declare(strict_types=1);

namespace TheChoice\Processor;

interface ProcessorInterface
{
    public function process($node);
}