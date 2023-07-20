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
 * @date   2019-03-15
 */
namespace AstraPrefixed\GetAstra\Plugins\Scanner\Services;

use AstraPrefixed\GetAstra\Client\Helper\Cms\AbstractCmsHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\ConfigHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\DBHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\ScanRelayHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\ServerHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\StatusHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Models\IndexedFile;
use AstraPrefixed\GetAstra\Plugins\Scanner\Models\Issue;
use AstraPrefixed\GetAstra\Plugins\Scanner\Models\KnownFile;
use AstraPrefixed\GetAstra\Plugins\Scanner\Models\Scan;
use AstraPrefixed\GetAstra\Plugins\Scanner\Models\Signature;
use AstraPrefixed\Slim\Http\Request;
use AstraPrefixed\Slim\Http\Response;
class ScanService
{
    public static $peakMemAtStart = 0;
    public static function scanMain(Request $request, Response $response, array $args, $scannerOptions, $isNudgeCall = \false)
    {
        self::$peakMemAtStart = \memory_get_peak_usage(\true);
        //TODO Check database connection
        //TODO Check if SchemaExists
        $receivedCronKey = $request->getParam('cronKey');
        if (\false !== \strpos($receivedCronKey, '/do/testAjax')) {
            $receivedCronKey = \str_replace('/do/testAjax', '', $receivedCronKey);
        }
        if (!$isNudgeCall) {
            self::checkCronKey($receivedCronKey);
        }
        //roller = new FileScanner();
        ConfigHelper::delete('scanStartAttempt');
        $isFork = '1' == $request->getParam('isFork') || $isNudgeCall ? \true : \false;
        if (!$isFork) {
            StatusHelper::add(4, 'info', 'Checking if scan is already running');
            if (!ConfigHelper::getScanLock()) {
                self::errorExit('There is already a scan running.');
            }
            ConfigHelper::updateScanStillRunning();
            ConfigHelper::set('peakMemory', 0, 'no');
            ConfigHelper::set('lowResourceScanWaitStep', \false);
        }
        //StatusHelper::add(4, 'info', "Requesting max memory");
        ServerHelper::requestMaxMemory();
        StatusHelper::add(4, 'info', 'Setting up error handling environment');
        \set_error_handler(array(self::class, 'error_handler'), \E_RECOVERABLE_ERROR);
        \register_shutdown_function(array(self::class, 'shutdown'));
        /* Start the scan */
        StatusHelper::add(4, 'info', 'Setting up scanRunning and starting scan');
        @\error_reporting(\E_ALL);
        ServerHelper::iniSet('display_errors', 'On');
        // Update running status to Astra
        $relay = new ScanRelayHelper();
        $relay->sendScanStatus('run');
        try {
            if ($isFork) {
                // This is a forkedCall
                StatusHelper::add(4, 'info', 'It is a forked call');
                $scan = new ScanEngine();
                StatusHelper::add(4, 'info', 'Loading the data since it is forked');
            } else {
                // FirstTime Call
                StatusHelper::add(4, 'info', 'Starting the scan for the first time');
                self::prepareScan();
                StatusHelper::add(1, 'info', 'Contacting Astra to initiate scan');
                $malwarePrefixesHash = '';
                $coreHashesHash = '';
                //ConfigHelper::set('startTime', time());
                ConfigHelper::set('filesIndexed', '', 'no');
                ConfigHelper::set('indexSize', 0, 'no');
                ConfigHelper::set('startRequireRemoteStart', \false);
                ConfigHelper::set('filesToScan', '');
                ConfigHelper::set('jobList', '', 'no');
                ConfigHelper::set('lastScanCompleted', '', 'no');
                ConfigHelper::set('lastScanFailureType', '', 'no');
                $scan = new ScanEngine();
                //$scan->deleteNewIssues();
            }
            $scan->setIgnoredPathsAndChecksums($scannerOptions);
            $scan->go();
        } catch (\Exception $e) {
            ConfigHelper::clearScanLock();
            $peakMemory = self::logPeakMemory();
            StatusHelper::add(2, 'info', 'Scanner used ' . ServerHelper::formatBytes($peakMemory - self::$peakMemAtStart) . ' of memory for scan. Server peak memory usage was: ' . ServerHelper::formatBytes($peakMemory));
            StatusHelper::add(2, 'error', 'Scan terminated with error: ' . $e->getMessage());
            exit;
        }
        ConfigHelper::clearScanLock();
    }
    public static function prepareScan()
    {
        //DBHelper::vacuumDB();
        KnownFile::query()->truncate();
        IndexedFile::query()->truncate();
        Issue::query()->truncate();
        $relay = new ScanRelayHelper();
        // Import KnownFiles checksums
        $cmsAdapter = new AbstractCmsHelper();
        $cms = $cmsAdapter->getCms();
        $cmsName = $cms->getName();
        $cmsVersion = $cms->getVersion();
        StatusHelper::add(4, 'info', "CMS version: {$cmsName} - version: {$cmsVersion}");
        ConfigHelper::set('cms', '', 'yes');
        $cmsLocale = '';
        if ('wordpress' === $cmsName && \is_callable([$cms, 'getLocale'])) {
            $cmsLocale = $cms->getLocale();
        }
        if (!empty($cmsName) && \false !== $cmsName && \false !== $cmsVersion) {
            // Only fetch knownFile hashes if we were able to detect the CMS
            ConfigHelper::set('cms', $cmsName, 'yes');
            if (!empty($cmsLocale)) {
                $checksums = $relay->getChecksums($cmsName, $cmsVersion, $cmsLocale);
                $relay->sendCmsDetails($cmsName, $cmsVersion, $cmsLocale);
            } else {
                $checksums = $relay->getChecksums($cmsName, $cmsVersion);
                $relay->sendCmsDetails($cmsName, $cmsVersion);
            }
            StatusHelper::add(1, 'info', "CMS version: {$cmsName} - version: {$cmsVersion} - locale: {$cmsLocale} ");
            //$keys = json_encode(array_keys($checksums));
            //StatusHelper::add(1, 'info', "checksums fetched properly - {$keys}");
            if (isset($checksums['hydra:member'][0]['hashes'])) {
                $knownFiles = [];
                foreach ($checksums['hydra:member'][0]['hashes'] as $fileName => $hash) {
                    $knownFiles[] = ['path' => $fileName, 'md5' => $hash];
                }
                $chunks = \array_chunk($knownFiles, 499);
                // Checksums need to be in chunks of 500 to be inserted into SQLite
                foreach ($chunks as $chunk) {
                    //StatusHelper::add(1, 'info_debug', "chunk - {$chunk['path']}");
                    KnownFile::query()->insert($chunk);
                    // Batch insert each chunk
                }
                StatusHelper::add(1, 'info', 'Loaded ' . \count($knownFiles) . ' known files');
            } else {
                StatusHelper::add(2, 'info', 'Unable to get the checksums - ' . \json_encode($checksums));
            }
        }
        // Import Signatures
        $signatures = $relay->getSignatures();
        if (isset($signatures[0]['pattern'])) {
            Signature::query()->truncate();
            $importedSignatures = [];
            foreach ($signatures as $signature) {
                $importedSignatures[] = ['sig_id' => $signature['id'], 'pattern' => $signature['pattern'], 'name' => $signature['name'], 'description' => $signature['description'], 'scanType' => $signature['scanType'], 'logOnly' => $signature['logOnly'], 'createdAt' => '2011-11-11 11:11:11', 'category' => $signature['category']];
                // Hard-coding the date since the format received from symfony isn't compatible with mysql
                // $signature['createdAt']
            }
            $chunks = \array_chunk($importedSignatures, 124);
            // SQLite allows 999 variables in a single query so we chunk accordingly
            foreach ($chunks as $chunk) {
                Signature::query()->insert($chunk);
                // Batch insert each chunk
            }
            StatusHelper::add(1, 'info', 'Loaded ' . \count($importedSignatures) . ' malware signatures');
        } else {
            StatusHelper::add(1, 'info', 'Unable to decode the signatures received from Astra API: ' . \json_encode($signatures));
        }
    }
    public static function logPeakMemory()
    {
        $oldPeak = ConfigHelper::get('peakMemory', 0);
        $peak = \memory_get_peak_usage(\true);
        if ($peak > $oldPeak) {
            ConfigHelper::set('peakMemory', $peak, 'no');
            return $peak;
        }
        return $oldPeak;
    }
    public static function shutdown()
    {
        self::logPeakMemory();
    }
    public static function error_handler($errno, $errstr, $errfile, $errline)
    {
        if (\error_reporting() > 0) {
            if (\preg_match('/astra\\//', $errfile)) {
                $level = 1;
            } else {
                $level = 4;
            }
            StatusHelper::add($level, 'error', "{$errstr} ({$errno}) File: {$errfile} Line: {$errline}");
        }
        return \false;
    }
    protected static function checkCronKey($receivedCronKey)
    {
        StatusHelper::add(4, 'info', 'Scan engine received request.');
        StatusHelper::add(4, 'info', 'Fetching stored cronkey for comparison.');
        $expired = \false;
        $storedCronKey = self::storedCronKey($expired);
        $displayCronKey_received = isset($receivedCronKey) ? \preg_match('/^[a-f0-9]+$/i', $receivedCronKey) && 32 == \strlen($receivedCronKey) ? $receivedCronKey : '[invalid]' : '[none]';
        $displayCronKey_stored = !empty($storedCronKey) && !$expired ? $storedCronKey : '[none]';
        StatusHelper::add(4, 'info', \sprintf('Checking cronkey: %s (expecting %s)', $displayCronKey_received, $displayCronKey_stored));
        if (empty($receivedCronKey)) {
            StatusHelper::add(4, 'error', 'Malware scan script accessed directly, or a cronkey was not received.');
            echo 'If you see this message it means that the Malware Scanner is working correctly. You should not access this URL directly. It is part of the Astra security Suite and is designed for internal use only.';
            exit;
        }
        if ($expired) {
            self::errorExit('The key used to start a scan expired. The value is: ' . $expired . ' and split is: ' . $storedCronKey . ' and time is: ' . \time());
        }
        //keys only last 60 seconds and are used within milliseconds of creation
        if (!$storedCronKey) {
            StatusHelper::add(4, 'error', 'Astra could not find a saved cron key to start the scan so assuming it started and exiting.');
            exit;
        }
        StatusHelper::add(4, 'info', 'Checking saved cronkey against cronkey param');
        if (!\hash_equals($storedCronKey, $receivedCronKey)) {
            self::errorExit('Astra could not start a scan because the cron key does not match the saved key. Saved: ' . $storedCronKey . ' Sent: ' . $receivedCronKey . ' Current unexploded: ' . ConfigHelper::get('currentCronKey', \false));
        }
        ConfigHelper::set('currentCronKey', '');
        return \true;
    }
    private static function storedCronKey(&$expired = null)
    {
        $currentCronKey = ConfigHelper::get('currentCronKey', \false);
        if (empty($currentCronKey)) {
            if (null !== $expired) {
                $expired = \false;
            }
            return \false;
        }
        $savedKey = \explode(',', $currentCronKey);
        if (\time() - $savedKey[0] > 86400) {
            if (null !== $expired) {
                $expired = $savedKey[0];
            }
            return $savedKey[1];
        }
        if (null !== $expired) {
            $expired = \false;
        }
        return $savedKey[1];
    }
    private static function errorExit($msg)
    {
        StatusHelper::add(1, 'error', "Scan Engine Error: {$msg}");
        echo $msg;
        exit;
    }
}
