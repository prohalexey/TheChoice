<?php

namespace TheChoice\Contract;

interface BuilderInterface
{
    public function build(&$structure);

    public function getRootDir(): string;

    public function addLoadedFile(string $path);
}