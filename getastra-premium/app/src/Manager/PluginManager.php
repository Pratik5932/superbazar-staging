<?php

/**
 * This file is part of the Astra Security Suite.
 *
 *  Copyright (c) 2019 (https://www.getastra.com/)
 *
 *  For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
/**
 * @author HumansofAstra-WZ <help@getastra.com>
 * @date   2019-03-10
 */
namespace AstraPrefixed\GetAstra\Client\Manager;

use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
use AstraPrefixed\GetAstra\Client\Helper\PluginInterface;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Log\LoggerInterface;
use AstraPrefixed\Psr\SimpleCache\CacheInterface;
use AstraPrefixed\Slim\App;
class PluginManager
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * Path to the plugin directory, coming from Configuration class.
     *
     * @var string
     */
    private $pluginPath;
    /**
     * Plugin Routes.
     *
     * @var array
     */
    private $routes;
    /**
     * @var CacheInterface
     */
    private $options;
    /**
     * @var array
     */
    private $plugins;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var array
     */
    private $migrationPaths;
    /**
     * @var array
     */
    private $pluginMiddlewares;
    /**
     * Higher Priority Middleware gets attached last but actually runs first.
     *
     * @todo maybe store in settings ???
     *
     * @var array
     */
    private const MIDDLEWARE_PRIORITY = ['WafPlugin' => 1, 'BotPlugin' => 2, 'UploadScannerPlugin' => 3, 'VirtualPatchPlugin' => 4];
    private $commonHelper;
    /**
     * PluginManager constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->pluginPath = $container->get('settings')['plugins']['path'];
        $this->routes = [];
        $this->logger = $container->get('logger');
        $this->options = $container->get('options');
        $this->pluginMiddlewares = [];
        $this->commonHelper = new CommonHelper();
        if ($this->commonHelper->checkIfOptionsPopulated($this->options)) {
            $this->loadMiddlewarePluginsOnDisk();
        } else {
            //$this->logger->warning('Middleware modules not loaded due to missing options.');
        }
        //$this->initialize();
    }
    /**
     * Has to be called from outside this class for it to work.
     * This attaches all the middleware based plugins to Slim\App object acc. to
     * m/w priority.
     *
     * @param Slim\App $astraApp
     */
    public function activateMiddleware(App $astraApp)
    {
        if (0 === \count($this->pluginMiddlewares)) {
            return;
        }
        if (!$this->commonHelper->checkIfOptionsPopulated($this->options)) {
            //$this->logger->warning('Middleware modules not loaded due to missing options.');
            return;
        }
        $intersection = \array_intersect(\array_keys($this::MIDDLEWARE_PRIORITY), $this->pluginMiddlewares);
        $final = [];
        foreach ($intersection as $middlewareClass) {
            $final[$middlewareClass] = $this::MIDDLEWARE_PRIORITY[$middlewareClass];
        }
        \asort($final);
        foreach (\array_keys($final) as $middleware) {
            $path = $this->pluginPath . $middleware . \DIRECTORY_SEPARATOR . $middleware . '.php';
            if (\file_exists($path)) {
                include_once $path;
                $astraApp->add(new $middleware($this->container));
            } else {
                $this->logger->warning("Plugin installed, file not found", [$middleware]);
            }
        }
    }
    private function loadMiddlewarePluginsOnDisk()
    {
        $pluginMiddlewares = $this->options->get('pluginMiddlewares' . GATEKEEPER_VERSION);
        if (\is_array($pluginMiddlewares) && \count($pluginMiddlewares) > 0) {
            $this->pluginMiddlewares = $pluginMiddlewares;
            return;
        }
        $scan = \scandir($this->pluginPath);
        foreach ($scan as $file) {
            if (\is_dir($this->pluginPath . $file)) {
                $checkFile = $this->pluginPath . $file . \DIRECTORY_SEPARATOR . $file . '.php';
                if ('Scanner' === $file) {
                    //scanner not loaded using plugin manager, instead initialized in dependencies.php
                    continue;
                }
                if (\file_exists($checkFile)) {
                    include_once $checkFile;
                    $instantiatedPlugin = new $file($this->container);
                    if ($instantiatedPlugin instanceof PluginInterface) {
                        if (\method_exists($instantiatedPlugin, 'isMiddlewareBasedPlugin')) {
                            if ($instantiatedPlugin->isMiddlewareBasedPlugin()) {
                                $this->pluginMiddlewares[] = $file;
                            }
                        }
                    } else {
                        $this->logger->error('Plugin in datastore does not implement PluginInterface: ' . $instantiatedPlugin->getName());
                    }
                }
            }
        }
        $this->options->set('pluginMiddlewares' . GATEKEEPER_VERSION, $this->pluginMiddlewares);
    }
    private function initialize()
    {
        if ($plugins = $this->options->get('plugins')) {
            foreach ($plugins as $name => $plugin) {
                if (isset($plugin['fqcn'], $plugin['path'])) {
                    /** @noinspection PhpIncludeInspection */
                    include $this->pluginPath . $plugin['path'];
                    $instantiatedPlugin = new $plugin['fqcn']($this->container);
                    if ($instantiatedPlugin instanceof PluginInterface) {
                        $this->activatePlugin($instantiatedPlugin, $plugin['fqcn'], $plugin['path']);
                    } else {
                        $this->logger->error('Plugin in datastore does not implement PluginInterface: ' . $plugin['fqcn']);
                    }
                } else {
                    $this->logger->warning('Malformed plugin details found in local datastore - ' . $plugin['fqcn'] ?? 'no FQCN - ' . $plugin['path'] ?? 'no path');
                }
            }
        } else {
            $this->logger->warning('No plugins installed');
        }
    }
    public function getRoutes() : array
    {
        return $this->routes;
    }
    public function getMigrationPaths() : array
    {
        return $this->migrationPaths;
    }
    private function activatePlugin(PluginInterface $plugin, string $fqcn, string $path) : void
    {
        $this->routes[$plugin->getName()] = $plugin->getRoutes();
        $this->migrationPaths[] = $plugin->getMigrationDirPath();
        $this->plugins[$plugin->getName()] = ['fqcn' => $fqcn, 'path' => $path, 'active' => \true];
    }
    public function getPlugins() : array
    {
        return $this->plugins;
    }
    public function getActivePlugins() : array
    {
        return \array_filter($this->plugins, function ($plugin) {
            return $plugin['active'];
        });
    }
    public function getDisabledPlugins() : array
    {
        return \array_filter($this->plugins, function ($plugin) {
            return !$plugin['active'];
        });
    }
    public function getPluginMiddlewares() : array
    {
        return $this->pluginMiddlewares;
    }
}
