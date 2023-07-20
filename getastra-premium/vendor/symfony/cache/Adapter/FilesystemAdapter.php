<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace AstraPrefixed\Symfony\Component\Cache\Adapter;

use AstraPrefixed\Symfony\Component\Cache\PruneableInterface;
use AstraPrefixed\Symfony\Component\Cache\Traits\FilesystemTrait;
class FilesystemAdapter extends AbstractAdapter implements PruneableInterface
{
    use FilesystemTrait;
    /**
     * @param string      $namespace
     * @param int         $defaultLifetime
     * @param string|null $directory
     */
    public function __construct($namespace = '', $defaultLifetime = 0, $directory = null)
    {
        parent::__construct('', $defaultLifetime);
        $this->init($namespace, $directory);
    }
}