<?php

namespace AstraPrefixed\GetAstra\Client\Service;

use AstraPrefixed\Doctrine\Common\Cache\ApcCache;
use AstraPrefixed\Doctrine\Common\Cache\SQLite3Cache;
use AstraPrefixed\GetAstra\Client\Helper\OptionProviderInterface;
use AstraPrefixed\Psr\Cache\CacheItemPoolInterface;
use AstraPrefixed\Psr\SimpleCache\CacheInterface;
use RuntimeException;
//use SQLite3;
use AstraPrefixed\Symfony\Component\Cache\Adapter\AdapterInterface;
use AstraPrefixed\Symfony\Component\Cache\Adapter\ApcuAdapter;
use AstraPrefixed\Symfony\Component\Cache\Adapter\ChainAdapter;
use AstraPrefixed\Symfony\Component\Cache\Adapter\DoctrineAdapter;
use AstraPrefixed\Symfony\Component\Cache\Adapter\FilesystemAdapter;
use AstraPrefixed\Symfony\Component\Cache\Adapter\PdoAdapter;
use AstraPrefixed\Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use AstraPrefixed\Symfony\Component\Cache\Simple\Psr6Cache;
class OptionService implements OptionProviderInterface
{
    private $pdo_sqlite;
    private $sqlite3;
    private $opcache;
    private $apc;
    private $apcu;
    private $fileSystemUsedForVolatile = \false;
    public function __construct()
    {
        $this->pdo_sqlite = \extension_loaded('pdo_sqlite');
        $this->sqlite3 = \extension_loaded('sqlite3');
        if (\function_exists('opcache_get_status')) {
            $this->opcache = \false !== @\opcache_get_status(\false);
        } else {
            $this->opcache = \false;
        }
        if ($this->apc = \ini_get('apc.enabled')) {
            $this->apcu = \version_compare('3.1.6', \phpversion('apc')) > 0;
        } else {
            $this->apcu = \false;
        }
    }
    public function getPersistentOptionsPool(string $name) : CacheItemPoolInterface
    {
        return $this->createOptionsCache($name, \true);
    }
    public function getVolatileOptionsPool(string $name) : CacheItemPoolInterface
    {
        return $this->createOptionsCache($name, \false);
    }
    public function getPersistentOptions(string $name) : CacheInterface
    {
        return new Psr6Cache($this->createOptionsCache($name, \true));
    }
    public function getVolatileOptions(string $name) : CacheInterface
    {
        return new Psr6Cache($this->createOptionsCache($name, \false));
    }
    private function createOptionsCache(string $name, bool $isPersistent = \true) : AdapterInterface
    {
        if ($isPersistent) {
            $volatile = $this->createVolatileCache($name);
            if (!$this->fileSystemUsedForVolatile) {
                return new ChainAdapter([$volatile, $this->createPersistentCache($name)]);
            } else {
                return $volatile;
            }
        } else {
            return $this->createVolatileCache($name);
        }
    }
    private function createVolatileCache(string $name) : AdapterInterface
    {
        if ($this->apcu) {
            return new ApcuAdapter($name, 0);
        } elseif ($this->apc) {
            return new DoctrineAdapter(new ApcCache(), $name, 0);
        } elseif ($this->opcache) {
            //return new PhpFilesAdapter($name, 0, ASTRAROOT.'var/cache/options');
            return new PhpFilesAdapter($name, 0, ASTRA_STORAGE_ROOT . 'cache');
        } elseif ($this->pdo_sqlite && $this->sqlite3) {
            return new PdoAdapter('sqlite::memory:', $name, 0);
        } else {
            //return new FilesystemAdapter($name, 0, ASTRAROOT.'var/cache/options');
            $this->fileSystemUsedForVolatile = \true;
            return new FilesystemAdapter($name, 0, ASTRA_STORAGE_ROOT . 'cache');
        }
    }
    private function createPersistentCache(string $name) : AdapterInterface
    {
        if ($this->sqlite3) {
            $sqlitePath = ASTRA_STORAGE_ROOT . 'cache/' . $name . '.sqlite';
            $db = new \SQLite3($sqlitePath);
            $db->busyTimeout(5000);
            return new DoctrineAdapter(new SQLite3Cache($db, $name), $name, 0);
        } else {
            return new FilesystemAdapter($name, 0, ASTRA_STORAGE_ROOT . 'cache');
        }
    }
}
