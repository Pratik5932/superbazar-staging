<?php

namespace AstraPrefixed\GetAstra\Client\Helper;

use AstraPrefixed\Psr\Cache\CacheItemPoolInterface;
use AstraPrefixed\Psr\SimpleCache\CacheInterface;
/**
 * Interface OptionProviderInterface.
 */
interface OptionProviderInterface
{
    public function getPersistentOptionsPool(string $name) : CacheItemPoolInterface;
    public function getVolatileOptionsPool(string $name) : CacheItemPoolInterface;
    public function getPersistentOptions(string $name) : CacheInterface;
    public function getVolatileOptions(string $name) : CacheInterface;
}
