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

use AstraPrefixed\Psr\Cache\CacheException as Psr6CacheInterface;
use AstraPrefixed\Psr\SimpleCache\CacheException as SimpleCacheInterface;
class CacheException extends \Exception implements Psr6CacheInterface, SimpleCacheInterface
{
}
