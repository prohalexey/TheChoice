<?php

namespace TheChoice\Factory;

use TheChoice\Builder\JsonBuilder;
use TheChoice\Builder\YamlBuilder;
use TheChoice\Contract\BuilderInterface;
use TheChoice\Contract\NodeFactoryInterface;

class NodeRequireFactory implements NodeFactoryInterface
{
    public function build(BuilderInterface $builder, array &$structure)
    {
        self::validate($builder, $structure);

        $extension = pathinfo($structure['path'], PATHINFO_EXTENSION);

        $operatorFactory = new OperatorFactory();

        switch($extension) {
            case 'json':
                $newBuilder = new JsonBuilder($operatorFactory);
                break;
            case 'yaml';
                $newBuilder = new YamlBuilder($operatorFactory);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown filetype of required file: %s, included file: "%s"', $extension, $structure['path']));
        }

        $newBuilder->setRootDir($builder->getRootDir());

        return $newBuilder->parseFile($structure['path']);
    }

    private static function validate(BuilderInterface $builder, array &$structure)
    {
        if (!array_key_exists('path', $structure)) {
            throw new \LogicException('The "file" property is absent in require operator!');
        }

        if (!is_string($structure['path'])) {
            throw new \LogicException('The "file" property must be string type!');
        }

        if (!$structure['path']) {
            throw new \InvalidArgumentException('File name is empty');
        }

        if (!file_exists($builder->getRootDir() . $structure['path'])) {
            throw new \InvalidArgumentException(sprintf('File not found "%s"',  $structure['path']));
        }

        $builder->addLoadedFile($builder->getRootDir() . $structure['path']);
    }
}