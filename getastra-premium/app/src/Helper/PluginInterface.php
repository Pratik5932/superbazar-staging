<?php

namespace AstraPrefixed\GetAstra\Client\Helper;

use AstraPrefixed\Psr\Container\ContainerInterface;
/**
 * PluginInterface - must be implemented by all Gatekeeper plugins.
 */
interface PluginInterface
{
    /**
     * Return plugin name - must match name of implementing class.
     *
     * @return string
     */
    public function getName();
    /**
     * Return plugin version string (semver format).
     *
     * @return string
     */
    public function getVersion();
    /**
     * Return path to plugin's migrations folder.
     *
     * @return string
     */
    public function getMigrationDirPath();
    /**
     * Return path to plugin's route file.
     *
     * @return array
     */
    public function getRoutes();
    /**
     * Whether the plugin is a request blocker and should be run on every request.
     *
     * @return bool
     */
    public function isRequestBlocker();
    /**
     * Whether the plugin uses the Astra API.
     *
     * @return bool
     */
    public function isApiUser();
    /**
     * Plugin Constructor.
     */
    public function __construct(ContainerInterface $container);
}
