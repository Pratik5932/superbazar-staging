<?php

namespace AstraPrefixed\GetAstra\Client\Service\Database;

use AstraPrefixed\Illuminate\Database\Capsule\Manager;
use AstraPrefixed\Phinx\Config\Config;
use AstraPrefixed\Phinx\Migration\Manager as MigrationManager;
use AstraPrefixed\Pimple\Container;
use AstraPrefixed\Pimple\ServiceProviderInterface;
use AstraPrefixed\Symfony\Component\Console\Input\StringInput;
use AstraPrefixed\Symfony\Component\Console\Output\NullOutput;
class EloquentServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $phinx = new MigrationManager(new Config($pimple['settings']['phinx']), new StringInput(' '), new NullOutput());
        if (!$this->testDatabase($phinx)) {
            $this->createDatabase($phinx);
        }
        $capsule = new Manager();
        $config = $pimple['settings']['database'];
        if ('sqlite' === $config['driver']) {
            $capsule->addConnection(['driver' => $config['driver'], 'database' => $config['database'], 'charset' => 'utf8', 'collation' => 'utf8_unicode_ci']);
        } else {
            $capsule->addConnection(['driver' => $config['driver'], 'database' => $config['database'], 'charset' => 'utf8', 'collation' => 'utf8_unicode_ci', 'host' => $config['host'], 'port' => $config['port'], 'username' => $config['username'], 'password' => $config['password'], 'prefix' => $config['prefix']]);
        }
        // Make this Capsule instance available globally via static methods... (optional)
        $capsule->setAsGlobal();
        // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
        $capsule->bootEloquent();
        $pimple['db'] = function ($c) use($capsule) {
            return $capsule;
        };
        $pimple['schema'] = function ($c) use($capsule) {
            return $capsule::schema();
        };
    }
    protected function testDatabase($phinx)
    {
        $status = $phinx->printStatus('production');
        return !($status['hasMissingMigration'] || $status['hasDownMigration']);
    }
    protected function createDatabase($phinx)
    {
        $phinx->migrate('production');
    }
}
