<?php

namespace AstraPrefixed;

/**
 * Created by PhpStorm.
 * User: anandakrishna
 * Date: 2019-03-09
 * Time: 21:15.
 */
use AstraPrefixed\GetAstra\Plugins\Scanner\Controllers\Option\OptionController;
use AstraPrefixed\GetAstra\Plugins\Scanner\Controllers\Report\IssueController;
use AstraPrefixed\GetAstra\Plugins\Scanner\Controllers\Scan\ScanController;
use AstraPrefixed\GetAstra\Plugins\Scanner\Controllers\Scan\StatusController;
use AstraPrefixed\GetAstra\Plugins\Scanner\Controllers\Scan\TestController;
//var_dump('yo?');exit;
$astraApp->group('/api/plugins/scanner', function () {
    //$optionalAuth = $this->getContainer()->get('optionalAuth');
    //$apiAuth = $this->getContainer()->get('apiAuth');
    $this->get('/scan', ScanController::class . ':index')->setName('plugins.scanner.scans.index');
    $this->post('/scan', ScanController::class . ':start')->setName('plugins.scanner.scans.start');
    $this->delete('/scan', ScanController::class . ':stop')->setName('plugins.scanner.scans.stop');
    $this->post('/nudge', ScanController::class . ':nudge')->setName('plugins.scanner.scans.nudge');
    /* Scan */
    //$this->post('/scan/do/testAjax', ScanController::class . ':testAjax')->setName('plugins.scanner.testAjax');
    //$this->get('/scan/do', ScanController::class . ':doScan')->setName('plugins.scanner.scans.perform');
    $this->get('/scan/health', ScanController::class . ':health')->setName('plugins.scanner.scans.health');
    /* Scan Status */
    $this->get('/status', StatusController::class . ':index')->setName('plugins.scanner.status.index');
    $this->delete('/status', StatusController::class . ':destroy')->setName('plugins.scanner.status.destroy');
    /* Issues */
    $this->get('/issues', IssueController::class . ':index')->setName('plugins.scanner.issues.index');
    $this->delete('/issues', IssueController::class . ':destroy')->setName('plugins.scanner.issues.destroy');
    $this->post('/deleteFile', IssueController::class . ':deleteFile')->setName('plugins.scanner.issue.deletefile');
    //$this->put('/scans/{id}', ScanController::class . ':update')->setName('plugins.scanner.scans.update');
    $this->get('/options', OptionController::class . ':index')->setName('option.index');
    $this->post('/options', OptionController::class . ':store')->setName('option.store');
    $this->get('/options/{name}', OptionController::class . ':show')->setName('option.show');
    $this->put('/options/{name}', OptionController::class . ':update')->setName('option.update');
    $this->delete('/options/{name}', OptionController::class . ':destroy')->setName('option.destroy');
});
$astraApp->group('/api/plugins/scanner', function () {
    $this->post('/scan/do/testAjax', ScanController::class . ':testAjax')->setName('plugins.scanner.testAjax');
    $this->get('/scan/do', ScanController::class . ':doScan')->setName('plugins.scanner.scans.perform');
});
