<?php

namespace AstraPrefixed;

// DIC configuration
/* @var Pimple\Container $container */
use AstraPrefixed\GetAstra\Client\Exception\ErrorHandler;
use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
use AstraPrefixed\GetAstra\Client\Helper\UrlHelper;
use AstraPrefixed\GetAstra\Client\Manager\PluginManager;
use AstraPrefixed\GetAstra\Client\Middleware\Responder;
use AstraPrefixed\GetAstra\Client\Service\Database\EloquentServiceProvider;
use AstraPrefixed\GetAstra\Client\Service\LogServiceProvider;
use AstraPrefixed\GetAstra\Client\Service\OAuthServiceProvider;
use AstraPrefixed\GetAstra\Client\Service\OptionServiceProvider;
use AstraPrefixed\GetAstra\Client\Service\SiteSettingsServiceProvider;
use AstraPrefixed\GetAstra\Client\Service\UpdateServiceProvider;
use AstraPrefixed\GetAstra\Plugins\Scanner;
use AstraPrefixed\League\Fractal\Manager;
use AstraPrefixed\League\Fractal\Serializer\ArraySerializer;
use AstraPrefixed\Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use AstraPrefixed\Monolog\Handler\FingersCrossedHandler;
use AstraPrefixed\Monolog\Logger;
use AstraPrefixed\Sentry\ClientBuilder;
use AstraPrefixed\Sentry\Monolog\Handler;
use AstraPrefixed\Sentry\State\Hub;
use AstraPrefixed\Slim\Http\Environment;
use AstraPrefixed\Slim\Http\Response;
use AstraPrefixed\Slim\Http\Uri;
use AstraPrefixed\Slim\Views\PhpRenderer;
$astraContainer = $astraApp->getContainer();
$GLOBALS['astraContainer'] =& $astraContainer;
//for openCart3
//silencing the original 404 slim handler when any endpoint is hit besides the slim apis,
//to prevent "headers already sent error"
unset($astraContainer['notFoundHandler']);
$astraContainer['notFoundHandler'] = function ($c) {
    return function ($request, $response) {
        //instead of printing 404 not found slim template, let the cms handle the output.
        $response = new \AstraPrefixed\Slim\Http\Response(301);
        return $response;
    };
};
$astraContainer['logger'] = function ($c) {
    $settings = $c->get('settings');
    $sentryClient = ClientBuilder::create(['dsn' => $settings['sentry']['dsn']])->getClient();
    $logLevel = \intval(CommonHelper::customGetEnv('ASTRA_APP_LOG_LEVEL'));
    return new Logger($settings['app']['name'], [new FingersCrossedHandler(new Handler(new Hub($sentryClient)), new ErrorLevelActivationStrategy($logLevel), 0, \true, \true, Logger::DEBUG)], [new Monolog\Processor\WebProcessor()]);
};
// Error Handler
$astraContainer['errorHandler'] = function ($c) {
    return new ErrorHandler($c['settings']['displayErrorDetails']);
};
// 404 handler so that there is no response if an invalid Astra API url is reached
/*$astraContainer['notFoundHandler'] = function ($c) {
    return '';
};*/
unset($astraContainer['errorHandler']);
// Create SQLite Database File if required
if ('sqlite' == $astraContainer['settings']['database']['driver'] && !\file_exists($astraContainer['settings']['database']['database'])) {
    \touch($astraContainer['settings']['database']['database']);
}
\define('ASTRA_DB_DRIVER', $astraContainer['settings']['database']['driver']);
// App Service Providers
$astraContainer->register(new EloquentServiceProvider());
$astraContainer->register(new OptionServiceProvider());
$astraContainer->register(new LogServiceProvider());
$astraContainer->register(new OAuthServiceProvider());
$astraContainer->register(new UpdateServiceProvider());
$astraContainer->register(new SiteSettingsServiceProvider());
//(only for Malware Scanner)
$astraSchemaCheck = new \AstraPrefixed\GetAstra\Client\Middleware\DBSchema($astraContainer);
if (!$astraSchemaCheck->exists()) {
    $astraSchemaCheck->createIfMissing();
}
//Scanner Plugin Init and Schema (only for Malware Scanner)
$astraScanner = \ASTRAROOT . 'plugins/Scanner/Scanner.php';
if (\ASTRA_API_ROUTE && \file_exists($astraScanner)) {
    include_once $astraScanner;
    $astraScanner = new GetAstra\Plugins\Scanner($astraContainer);
}
// Fractal (only for Malware Scanner)
$astraContainer['fractal'] = function ($c) {
    $manager = new Manager();
    $manager->setSerializer(new ArraySerializer());
    return $manager;
};
//Validation (only for Malware Scanner)
$astraContainer['validator'] = function ($c) {
    \AstraPrefixed\Respect\Validation\Validator::with('AstraPrefixed\\GetAstra\\Plugins\\Scanner\\Validation\\Rules');
    return new \AstraPrefixed\GetAstra\Plugins\Scanner\Validation\Validator();
};
// Plugins
$astraContainer['plugins'] = function ($c) {
    return new PluginManager($c);
};
// Attach middlewarebased Plugins
$astraContainer['plugins']->activateMiddleware($astraApp);
//Runs 3rd - Test module
//$astraApp->add(new \GetAstra\Client\Plugin\Waf\TestAfterMiddleware($astraApp->getContainer()));
//Runs 2nd - Core module
$astraApp->add(new \AstraPrefixed\GetAstra\Client\Middleware\IpRuleMiddleware($astraApp->getContainer()));
//Runs 1st - Core module
$astraApp->add(new \AstraPrefixed\GetAstra\Client\Middleware\ChecksMiddleware($astraApp->getContainer()));
$astraContainer['view'] = function ($c) {
    return new \AstraPrefixed\Slim\Views\PhpRenderer(\ASTRAROOT . 'app/templates/');
};
$astraContainer['csrf'] = function ($c) {
    $guard = new \AstraPrefixed\Slim\Csrf\Guard();
    $guard->setFailureCallable(function ($request, $response, $next) {
        $request = $request->withAttribute('csrf_status', \false);
        return $next($request, $response);
    });
    return $guard;
};
$astraApp->add(new Responder($astraContainer));
