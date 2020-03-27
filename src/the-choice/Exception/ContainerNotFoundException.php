<?php

declare(strict_types=1);

namespace TheChoice\Exception;

use Psr\Container\NotFoundExceptionInterface;

class ContainerNotFoundException extends GeneralException implements NotFoundExceptionInterface
{
}