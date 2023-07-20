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
 * @date   2019-03-14
 */
namespace AstraPrefixed\GetAstra\Plugins\Scanner\Services;

use AstraPrefixed\Curl\Curl;
use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
use AstraPrefixed\GetAstra\Client\Helper\StringHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\ConfigHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\DBHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\ScanRelayHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\ServerHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\StatusHelper;
class ScanEngine
{
    private $forkRequested = \false;
    private $hasher = \false;
    private $startTime = 0;
    private $cycleStartTime = 0;
    public $maxExecTime = \false;
    private $scanner = \false;
    private $scanQueue = [];
    private $hoover = \false;
    private $scanData = [];
    private $malwarePrefixesHash = [];
    private $coreHashesHash = [];
    private $forkCount = 0;
    private $metrics = [];
    private $jobList = [];
    private $ignoredFileList = [];
    public function __construct($malwarePrefixesHash = '', $coreHashesHash = '')
    {
        $this->startTime = \time();
        $this->recordMetric('scan', 'start', $this->startTime);
        $this->maxExecTime = self::getMaxExecutionTime();
        $this->cycleStartTime = \time();
        $this->malwarePrefixesHash = $malwarePrefixesHash;
        $this->coreHashesHash = $coreHashesHash;
        $this->jobList = [];
        $jobList = ConfigHelper::get('jobList', []);
        if (empty($jobList)) {
            $jobs = $this->jobs();
            foreach ($jobs as $job) {
                if (\method_exists($this, 'scan_' . $job . '_init')) {
                    foreach (['init', 'main', 'finish'] as $op) {
                        $this->jobList[] = $job . '_' . $op;
                    }
                } elseif (\method_exists($this, 'scan_' . $job)) {
                    $this->jobList[] = $job;
                }
            }
            ConfigHelper::set('jobList', $this->jobList);
        } else {
            $this->jobList = $jobList;
        }
    }
    public function setIgnoredPathsAndChecksums($scannerOptions)
    {
        //StatusHelper::add(2, 'info', 'August 4 - Ignored file list set in Scan engine - '.json_encode($scannerOptions));
        if (empty($scannerOptions) || \count($scannerOptions) == 0) {
            return;
        }
        $this->ignoredFileList['ignoredFiles'] = isset($scannerOptions['ignoredFiles']) ? $scannerOptions['ignoredFiles'] : null;
        $this->ignoredFileList['ignoredPaths'] = isset($scannerOptions['ignoredPaths']) ? $scannerOptions['ignoredPaths'] : null;
        return;
    }
    public function getIgnoredPathsAndChecksums()
    {
        return $this->ignoredFileList;
    }
    public static function startScan($isFork = \false)
    {
        if (!\defined('DONOTCACHEDB')) {
            \define('DONOTCACHEDB', \true);
        }
        if (!$isFork) {
            // It means that the scan is being initiated now.
            ConfigHelper::increment('totalScansRun');
            ConfigHelper::set('scanDuration', 0);
            // Total time taken to run the scan
            ConfigHelper::set('killRequested', 0);
            StatusHelper::add(4, 'info', 'Entering start scan routine');
            if (self::isRunning()) {
                StatusHelper::add(1, 'info', 'A scan is already running. Use the stop scan button if you would like to terminate the current scan.');
            }
            ConfigHelper::set('currentCronKey', '');
            //Ensure the cron key is cleared
        }
        $timeout = self::getMaxExecutionTime() - 2;
        //2 seconds shorter than max execution time which ensures that only 2 HTTP processes are ever occupied
        $testResult = '';
        if (!ConfigHelper::get('startScansRemotely', \false)) {
            try {
                $baseUrl = ConfigHelper::get('baseUrl', '');
                $testResult = AjaxService::textAjax($baseUrl, $timeout);
            } catch (Exception $e) {
                //Fall through to the remote start test below
            }
            StatusHelper::add(4, 'info', 'Test result of scan start URL fetch: ' . \var_export($testResult, \true));
        }
        $cronKey = CommonHelper::bigRandomHex();
        ConfigHelper::set('currentCronKey', \time() . ',' . $cronKey);
        /* Will be starting the scan */
        if (!empty($testResult) && \false !== \strstr($testResult, 'SCANTESTOK')) {
            //ajax requests can be sent by the server to itself
            $baseUrl = ConfigHelper::get('baseUrl');
            if (ASTRA_API_ROUTE) {
                $cronURL = '/do&isFork=' . ($isFork ? '1' : '0') . '&cronKey=' . $cronKey;
            } else {
                $cronURL = '/do?isFork=' . ($isFork ? '1' : '0') . '&cronKey=' . $cronKey;
            }
            $cronURL = $baseUrl . $cronURL;
            StatusHelper::add(4, 'info', "Starting cron with normal ajax at URL {$cronURL}");
            try {
                ConfigHelper::set('scanStartAttemptscanStartAttempt', \time());
                $curl = new Curl();
                $curl->setOpt(\CURLOPT_SSL_VERIFYHOST, \false);
                $curl->setOpt(\CURLOPT_SSL_VERIFYPEER, \false);
                //$curl->setRetry(3);
                //$curl->setHeader('Referer', false);
                $curl->setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36');
                $curl->setReferrer('https://www.getastra.com/?scanfork');
                // $curl->setOpt(CURLOPT_TIMEOUT_MS, 10);
                $curl->setTimeout(3);
                $curl->get($cronURL);
                if ($curl->error) {
                    $error_message = $curl->errorCode . ': ' . $curl->errorMessage;
                    ConfigHelper::set('lastScanCompleted', 'There was an ' . ($error_message ? '' : 'unknown ') . 'error starting the scan' . ($error_message ? ": {$error_message}" : '.'));
                    ConfigHelper::set('lastScanFailureType', 'scanner.callbackfailed');
                    if (\CURLE_OPERATION_TIMEDOUT !== $curl->errorCode) {
                        $relay = new ScanRelayHelper();
                        $message = 'Error (' . $curl->errorCode . ') while forking: ' . $error_message;
                        $message = StringHelper::truncate($message, 255);
                        StatusHelper::add(4, 'info', 'There was an ' . ($error_message ? '' : 'unknown ') . 'error starting the scan' . ($error_message ? ": {$error_message}" : '.'));
                        // Since we remotely nudge stuck scans, no need to move it to a failed state
                        // $relay->sendScanStatus('fail', 'Scan unable to start: '.$message);
                    }
                } else {
                    $message = '';
                    //if (method_exists($curl, 'response')) {
                    $message = $curl->response;
                    //}
                    StatusHelper::add(4, 'info', 'Scan completed for ' . $curl->url . ' with response ' . $message);
                }
                //TODO Notify SF about Scan Start
            } catch (\Exception $e) {
                ConfigHelper::set('lastScanCompleted', $e->getMessage());
                ConfigHelper::set('lastScanFailureType', 'scanner.callbackfailed');
                return \false;
            }
            StatusHelper::add(4, 'info', 'Scan process ended after forking.');
        } else {
            // Was unable to run the scan via cUrl to remotely start scan
            $relay = new ScanRelayHelper();
            //$relay->sendScanStatus('fail', 'Error while testing ajax: '.StringHelper::truncate($testResult, 255).'; triggering remote request.');
            ConfigHelper::set('scanRequireRemoteStart', \time());
            ConfigHelper::set('startScansRemotely', \true);
            $relay->sendBounceRequest($cronKey, $isFork, ConfigHelper::get('scanCode'));
            StatusHelper::add(4, 'info', 'Remote scan request placed');
        }
        return \false;
    }
    public static function requestKill()
    {
        ConfigHelper::set('killRequested', \time(), 'no');
    }
    public static function checkForKill()
    {
        $kill = (int) ConfigHelper::get('killRequested', 0);
        $math = \time() - \strtotime(\date('m/d/Y H:i:s', $kill)) < 600;
        //Kill lasts for 10 minutes
        if (0 !== $kill && $math) {
            StatusHelper::add(10, 'info', 'Kill and math: ' . \json_encode([$kill, $math]));
            ConfigHelper::set('killRequested', 0);
            StatusHelper::add(10, 'info', 'Previous scan was stopped successfully. Scan was stopped on administrator request');
            exit;
        }
    }
    /**
     * Returns whether or not a scan is running. A scan is considered running if the timestamp
     * under 'scanRunning' is within limits defined.
     *
     * @return bool
     */
    public static function isRunning()
    {
        $scanRunning = (int) ConfigHelper::get('scanRunning');
        $scanTimeout = (int) ConfigHelper::get('scanTimeoutMinutes');
        /*
        echo "ScanRunning: " . date('m/d/Y H:i:s', $scanRunning) . "\r\n";
        echo "Time Now: " . date('m/d/Y H:i:s', time()) . "\r\n";
        echo "Difference: " . (time() - strtotime(date('m/d/Y H:i:s', $scanRunning))) . "\r\n";
        echo "Timeout: " . ($scanTimeout * 60);
        die();
        */
        $timeoutCrossed = \time() - \strtotime(\date('m/d/Y H:i:s', $scanRunning)) < $scanTimeout * 60;
        return $scanRunning && $timeoutCrossed;
    }
    public static function getMaxExecutionTime($staySilent = \false)
    {
        $config = ConfigHelper::get('maxExecutionTime');
        $scanMinExecTime = 8;
        $scanMaxIniExecTime = 90;
        if (\is_numeric($config) && $config >= $scanMinExecTime) {
            if (!$staySilent) {
                StatusHelper::add(4, 'info', "getMaxExecutionTime() returning config value: {$config}");
            }
            return $config;
        }
        $ini = @\ini_get('max_execution_time');
        if (!$staySilent) {
            StatusHelper::add(4, 'info', "Got max_execution_time value from ini: {$ini}");
        }
        if (\is_numeric($ini) && $ini >= $scanMinExecTime) {
            if ($ini > $scanMaxIniExecTime) {
                if (!$staySilent) {
                    StatusHelper::add(4, 'info', "ini value of {$ini} is higher than value for {$scanMaxIniExecTime} (" . $scanMaxIniExecTime . '), reducing');
                }
                $ini = $scanMaxIniExecTime;
            }
            $ini = \floor($ini / 2);
            if (!$staySilent) {
                StatusHelper::add(4, 'info', "getMaxExecutionTime() returning half ini value: {$ini}");
            }
            return $ini;
        }
        if (!$staySilent) {
            StatusHelper::add(4, 'info', 'getMaxExecutionTime() returning default of: 15');
        }
        return 15;
    }
    public function lastScanTime()
    {
        return ConfigHelper::get('scanTime');
    }
    public function recordLastScanTime()
    {
        ConfigHelper::set('scanTime', \microtime(\true));
    }
    public static function storeState()
    {
        return \true;
    }
    public function shouldFork()
    {
        static $lastCheck = 0;
        if (\time() - $this->cycleStartTime > $this->maxExecTime) {
            return \true;
        }
        if ($lastCheck > \time() - $this->maxExecTime) {
            return \false;
        }
        $lastCheck = \time();
        ConfigHelper::updateScanStillRunning();
        self::checkForKill();
        $this->checkForDurationLimit();
        return \false;
    }
    public function forkIfNeeded()
    {
        ConfigHelper::updateScanStillRunning();
        self::checkForKill();
        $this->checkForDurationLimit();
        if (\time() - $this->cycleStartTime > $this->maxExecTime) {
            StatusHelper::add(4, 'info', 'Forking during hash scan to ensure continuity.');
            $this->fork();
        }
    }
    public function fork()
    {
        StatusHelper::add(4, 'info', 'Entered fork()');
        if (self::storeState()) {
            //$this->scanController->flushSummaryItems();
            StatusHelper::add(4, 'info', 'Calling startScan(true)');
            self::startScan(\true);
        }
        //Otherwise there was an error so don't start another scan.
        exit(0);
    }
    public function doScan()
    {
        StatusHelper::add(4, 'info', 'Doing Scan');
        while (\sizeof($this->jobList) > 0) {
            self::checkForKill();
            $jobFinished = \false;
            $jobName = $this->jobList[0];
            $callback = [$this, 'scan_' . $jobName];
            if (\is_callable($callback)) {
                StatusHelper::add('3', 'info', "Dispatching job: scan_{$jobName}");
                $jobFinished = \call_user_func($callback);
            }
            if (\true === $jobFinished) {
                \array_shift($this->jobList);
                //only shift once we're done because we may pause halfway through a job and need to pick up where we left off
            }
            ConfigHelper::set('jobList', $this->jobList);
            self::checkForKill();
            if ($this->forkRequested) {
                $this->fork();
            } else {
                $this->forkIfNeeded();
            }
        }
        /* End */
        $scanCompleted = ConfigHelper::get('scanCompleted', \false);
        if ($scanCompleted) {
            return;
        }
        $startTime = (int) ConfigHelper::get('startTime');
        $scanDuration = (\time() - $startTime) / 60;
        ConfigHelper::set('scanDuration', $scanDuration);
        ConfigHelper::set('scanCompleted', \true);
        ConfigHelper::set('startScansRemotely', \false);
        StatusHelper::add('2', 'info', 'Scan completed in ' . $scanDuration . ' min at ' . \date('m/d/Y H:i:s', \time()));
        $relay = new ScanRelayHelper();
        $relay->sendMetrics();
        DBHelper::vacuumDB();
        return;
    }
    public function removeJob($name = '')
    {
        if (empty($name)) {
            \array_shift($this->jobList);
        }
        ConfigHelper::set('jobList', $this->jobList);
    }
    public function deleteNewIssues($types = null)
    {
        $this->issues->deleteNew($types);
    }
    public function go()
    {
        try {
            self::checkForKill();
            $this->doScan();
            ConfigHelper::set('lastScanCompleted', 'ok');
            ConfigHelper::set('lastScanFailureType', \false);
            self::checkForKill();
            //updating this scan ID will trigger the scan page to load/reload the results.
            //$this->scanController->recordLastScanTime();
            //scan ID only incremented at end of scan to make UI load new results
            //$this->emailNewIssues();
            // Record Metrics about the scan
            $this->recordMetric('scan', 'duration', \time() - $this->startTime);
            $this->recordMetric('scan', 'memory', ConfigHelper::get('peakMemory', 0, \false));
            $this->submitMetrics();
            //TODO Notify SF about Scan status
        } catch (\Exception $e) {
            StatusHelper::add(5, 'error', 'Scan error: ' . $e->getMessage());
            ConfigHelper::set('lastScanCompleted', $e->getMessage());
            ConfigHelper::set('lastScanFailureType', 'scanner.general');
        }
    }
    public function submitMetrics()
    {
        if (ConfigHelper::get('shareMetrics', \true)) {
            //TODO Send Metrics to SF
        }
    }
    public function recordMetric($type, $key, $value, $singular = \true)
    {
        if (!isset($this->metrics[$type])) {
            $this->metrics[$type] = [];
        }
        if (!isset($this->metrics[$type][$key])) {
            $this->metrics[$type][$key] = [];
        }
        if ($singular) {
            $this->metrics[$type][$key] = $value;
        } else {
            $this->metrics[$type][$key][] = $value;
        }
    }
    public function checkForDurationLimit()
    {
        static $timeLimit = \false;
        if (\false === $timeLimit) {
            $timeLimit = \intval(ConfigHelper::get('scanMaxDuration', 0));
            if ($timeLimit < 1) {
                //$timeLimit = 10800; // Default maxScanDuration
                $timeLimit = 10800;
                // Default maxScanDuration
            }
        }
        $startTime = (int) ConfigHelper::get('startTime');
        if (\time() - $startTime > $timeLimit) {
            $error = 'The scan time limit of ' . StatusHelper::makeDuration($timeLimit) . ' has been exceeded and the scan will be terminated. This limit can be customized on the options page.';
            StatusHelper::add(10, 'info', $error);
            $relay = new ScanRelayHelper();
            $relay->sendMetrics();
            exit;
        }
    }
    private function scan_knownFiles_init()
    {
        $baseWPStuff = ['.htaccess', 'index.php', 'license.txt', 'readme.html', 'wp-activate.php', 'wp-admin', 'wp-app.php', 'wp-blog-header.php', 'wp-comments-post.php', 'wp-config-sample.php', 'wp-content', 'wp-cron.php', 'wp-includes', 'wp-links-opml.php', 'wp-load.php', 'wp-login.php', 'wp-mail.php', 'wp-pass.php', 'wp-register.php', 'wp-settings.php', 'wp-signup.php', 'wp-trackback.php', 'xmlrpc.php'];
        StatusHelper::add(4, 'info', 'Scanning path: ' . ASTRA_DOC_ROOT);
        $baseContents = \scandir(ASTRA_DOC_ROOT);
        if (!\is_array($baseContents)) {
            throw new \Exception("Astra could not read the contents of your base directory. This usually indicates your permissions are so strict that your web server can't read the website directory.");
        }
        $includeInKnownFilesScan = [];
        $scanOutside = (bool) ConfigHelper::get('scanOutsideFiles', \true);
        //echo $scanOutside;
        foreach ($baseContents as $file) {
            //Only include base files less than a meg that are files.
            if ('.' == $file || '..' == $file) {
                continue;
            }
            $fullFile = \rtrim(ASTRA_DOC_ROOT, '/') . '/' . $file;
            if (@\is_readable($fullFile) && (@\is_file($fullFile) && !ServerHelper::fileTooBig($fullFile) || @\is_dir($fullFile))) {
                if (!$scanOutside && !\in_array($file, $baseWPStuff)) {
                    continue;
                }
                $includeInKnownFilesScan[$file] = 1;
            }
        }
        ConfigHelper::set('totalFilesScanned', 0);
        //Since it's a new scan set the total as zero
        ConfigHelper::set('filesToScan', $includeInKnownFilesScan);
        return \true;
    }
    public function test()
    {
        //$this->scan_knownFiles_init();
        //$this->scan_knownFiles_main();
        //$this->scan_knownFiles_finish();
        //$this->scan_fileContents_init();
        $this->scan_fileContents_main();
    }
    private function scan_knownFiles_main()
    {
        $this->hasher = new HasherService(\strlen(ASTRA_DOC_ROOT), ASTRA_DOC_ROOT, $this);
        //$path = '/var/www/html/cms/wordpress/';
        //$this->hasher = new HasherService(strlen($path), $path, $this); //@todo delete before commit
        return $this->hasher->run($this);
    }
    private function scan_knownFiles_finish()
    {
        $this->hasher = new HasherService(\strlen(ASTRA_DOC_ROOT), ASTRA_DOC_ROOT, $this);
        //$path = '/var/www/html/cms/wordpress/';
        //$this->hasher = new HasherService(strlen($path), $path, $this); //@todo delete before commit
        return $this->hasher->passiveScan($this);
    }
    public function scan_fileContents_init()
    {
        StatusHelper::add(1, 'info', 'Scanning file contents for infections and vulnerabilities');
        ConfigHelper::set('totalFiles', 0);
        return \true;
    }
    public function scan_fileContents_main()
    {
        $scanner = new FileScanner(ASTRA_DOC_ROOT, $this);
        return $scanner->scan($this);
        //return true;
    }
    public function scan_fileContents_finish()
    {
        return \true;
    }
    public function jobs()
    {
        $preferredOrder = ['knownFiles' => (bool) ConfigHelper::get('scanCheck_knownFiles', \true), 'fileContents' => (bool) ConfigHelper::get('scanCheck_fileContents', \true), 'suspectedFiles' => (bool) ConfigHelper::get('scanCheck_suspectedFiles', \true)];
        $jobs = [];
        foreach ($preferredOrder as $job => $enabler) {
            if (\true === $enabler) {
                $jobs[] = $job;
            }
        }
        return $jobs;
    }
}
