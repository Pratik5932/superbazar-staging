<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace AstraPrefixed\Symfony\Component\Cache\Simple;

use AstraPrefixed\Symfony\Component\Cache\Traits\ApcuTrait;
class ApcuCache extends AbstractCache
{
    use ApcuTrait;
    /**
     * @param string      $namespace
     * @param int         $defaultLifetime
     * @param string|null $version
     */
    public function __construct($namespace = '', $defaultLifetime = 0, $version = null)
    {
        $this->init($namespace, $defaultLifetime, $version);
    }
}
