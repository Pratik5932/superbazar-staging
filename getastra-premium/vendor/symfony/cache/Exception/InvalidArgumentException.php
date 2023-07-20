<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace AstraPrefixed\Symfony\Component\Cache\Exception;

use AstraPrefixed\Psr\Cache\InvalidArgumentException as Psr6CacheInterface;
use AstraPrefixed\Psr\SimpleCache\InvalidArgumentException as SimpleCacheInterface;
class InvalidArgumentException extends \InvalidArgumentException implements Psr6CacheInterface, SimpleCacheInterface
{
}
