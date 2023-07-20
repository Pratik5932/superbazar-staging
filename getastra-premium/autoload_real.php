<?php

namespace AstraPrefixed;

use AstraPrefixed\Slim\App;
use AstraPrefixed\GetAstra\Client\Tclient\ApiException;
if (isset($astraApp)) {
    return;
}
include_once 'astraUpdateModule.php';
//Updater module final script
//include 'scripts/removeCache.php';
//clearAllCache(true);
//exit;
try {
    // Create AstraEnvironment
    include_once __DIR__ . '/app/environment.php';
    $astraEnvironment = new AstraEnvironment();
    $astraEnvironment->create();
    \define('ASTRA_DEBUG_MODE', \false);
    if (\ASTRA_DEBUG_MODE) {
        \ini_set('display_errors', 1);
        \ini_set('display_startup_errors', 1);
        \error_reporting(\E_ALL);
    }
    if (!\defined('ASTRA_API_ROUTE')) {
        \define('ASTRA_API_ROUTE', isset($_GET['astraRoute']));
    }
    $astraDependencyErrors = (require __DIR__ . '/dependencyChecker.php');
    if (isset($astraDependencyErrors['errors']) && \count($astraDependencyErrors['errors']) > 0) {
        if (\ASTRA_DEBUG_MODE) {
            \print_r($astraDependencyErrors);
            exit;
        } else {
            return;
        }
    }
    if (\ASTRA_API_ROUTE) {
        $_SERVER['X_ASTRA_ORIGINAL_REQUEST_URI'] = $_SERVER['REQUEST_URI'];
        $_SERVER['REQUEST_URI'] = \rtrim($_SERVER['SCRIPT_NAME'], '/') . '/' . \urldecode($_GET['astraRoute']);
    }
    \define('GATEKEEPER_VERSION', 'v2.1.47');
    if (\PHP_SAPI == 'cli-server') {
        // To help the built-in PHP dev server, check if the request was actually for
        // something which should probably be served as a static file
        $url = \parse_url($_SERVER['REQUEST_URI']);
        $file = __DIR__ . $url['path'];
        if (\is_file($file)) {
            return \false;
        }
    }
    require __DIR__ . '/vendor/scoper-autoload.php';
    // starting a GK session only if astraRoute, otherwise Opencart will throw error
    if (\ASTRA_API_ROUTE) {
        \session_start();
    }
    // Instantiate the app
    $astraSettings = (require __DIR__ . '/app/settings.php');
    // Create the astraApp
    $astraApp = new App($astraSettings);
    // Set up dependencies
    require __DIR__ . '/app/dependencies.php';
    // Register middleware
    require __DIR__ . '/app/middleware.php';
    // Register routes
    require __DIR__ . '/app/routes.php';
    // Run app
    $astraApp->run(!\ASTRA_DEBUG_MODE);
} catch (\Throwable $e) {
    if ($e instanceof ApiException && $e->getCode() == 401 && $astraApp->getContainer()->has('options')) {
        $astraApp->getContainer()->get('options')->delete('accessToken');
    }
    if (\ASTRA_DEBUG_MODE) {
        throw $e;
    } else {
        if (\ASTRA_API_ROUTE) {
            throw new \Exception("Plugin exception", 500);
        } else {
            // Silence
        }
    }
}
try {
    // Destroy Astra environment since we're almost done with GK
    $astraEnvironment->destroy();
} catch (\Throwable $e) {
    // Silence
}
\error_clear_last();
