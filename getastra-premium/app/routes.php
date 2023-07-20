<?php

namespace AstraPrefixed;

use AstraPrefixed\GetAstra\Client\Controller\Auth\LoginController;
use AstraPrefixed\GetAstra\Client\Controller\BaseController;
use AstraPrefixed\GetAstra\Client\Controller\KeepAlive\KeepAliveController;
use AstraPrefixed\GetAstra\Client\Controller\Option\OptionController;
use AstraPrefixed\GetAstra\Client\Controller\Plugin\PluginController;
use AstraPrefixed\GetAstra\Client\Controller\Status\StatusController;
use AstraPrefixed\GetAstra\Client\Controller\Update\UpdateController;
use AstraPrefixed\Slim\Http\Request;
use AstraPrefixed\Slim\Http\Response;
// Api Routes
$astraApp->group('/api', function () {
    //$this->post('/options/{name}', OptionController::class.':store')->setName('option.store');
    $this->get('/options/{name}', OptionController::class . ':show')->setName('option.show');
    //$this->put('/options/{name}', OptionController::class.':update')->setName('option.update');
    //$this->delete('/options/{name}', OptionController::class.':destroy')->setName('option.destroy');
    $this->get('/status', StatusController::class . ':show')->setName('status.show');
    //$this->get('/plugins', PluginController::class.':show')->setName('plugin.show');
    //$this->post('/plugins', PluginController::class.':add')->setName('plugin.add');
    $this->post('/update', UpdateController::class . ':triggerUpdate')->setName('update.trigger');
    $this->post('/keepalive', KeepAliveController::class . ':keepAlive')->setName('keepalive');
    $this->post('/updateSettings', UpdateController::class . ':updateSiteSettings')->setName('update.settings');
    //$this->get('/bulkUpdateOptions', UpdateController::class.':bulkOptionsSync')->setName('update.bulk');
    $this->get('/syncAll', UpdateController::class . ':syncAll')->setName('update.bulk2');
    //$this->get('/try', OptionController::class.':try')->setName('option.try');
});
$astraApp->group('/api', function () {
    $this->get('/login', LoginController::class . ':login')->setName('auth.login');
    $this->post('/directLogin', LoginController::class . ':directLogin')->setName('auth.directLogin');
    //$this->get('/stepTwo', LoginController::class.':stepTwo')->setName('auth.stepTwo');
    $this->post('/login', LoginController::class . ':token')->setName('auth.token');
})->add($astraApp->getContainer()->get('csrf'));
// Routes created by Plugins
//foreach ($routesFiles as $routesFile) {
//    include_once $routesFile;
//}
//All Routes
//$astraApp->any(
//    '/',
//    function (Request $request, Response $response, array $args) {
//        return ' MSG ';
//    }
//);
//Testing Route
$astraApp->any('/api/testing', function (Request $request, Response $response, array $args) {
    return 'TextDummyResponse';
    //$astraApp->getContainer()->get('view')->render($response,'blockPage.html.twig');
})->setName('block.page');
//$astraApp->any(
//    '/saveLoginInfo', BaseController::class.':loginApi'
//)->setName('loginTest.page');
$astraApp->get('/api', function (Request $request, Response $response, array $args) {
    return $response->withJson(['ping' => 'pong']);
});
$astraScannerRouteFilePath = \ASTRAROOT . '/plugins/Scanner/routes.php';
if (\file_exists($astraScannerRouteFilePath)) {
    include_once $astraScannerRouteFilePath;
} else {
    echo \json_encode(['error' => 'not found scanner routes']);
    exit;
}
