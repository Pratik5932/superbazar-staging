<?php

namespace AstraPrefixed\Doctrine\Common\Cache\Psr6;

use InvalidArgumentException;
use AstraPrefixed\Psr\Cache\InvalidArgumentException as PsrInvalidArgumentException;
/**
 * @internal
 */
final class InvalidArgument extends InvalidArgumentException implements PsrInvalidArgumentException
{
}
