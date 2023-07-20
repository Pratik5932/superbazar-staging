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

use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\ConfigHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\MalwareHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\ServerHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\StatusHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Models\Issue;
use AstraPrefixed\GetAstra\Plugins\Scanner\Models\KnownFile;
use AstraPrefixed\GetAstra\Plugins\Scanner\Models\Signature;
class FileScanner
{
    const EXCLUSION_PATTERNS_ALL = \PHP_INT_MAX;
    const EXCLUSION_PATTERNS_USER = 0x1;
    const EXCLUSION_PATTERNS_KNOWN_FILES = 0x2;
    const EXCLUSION_PATTERNS_MALWARE = 0x4;
    protected $path = '';
    protected $results = [];
    public $errorMsg = \false;
    protected $backtrackLimit = \false;
    protected $totalFilesScanned = 0;
    protected $startTime = \false;
    protected $lastStatusTime = \false;
    protected $patterns = [];
    protected $patternsMax = 0;
    protected $knownFiles = [];
    protected static $excludePatterns = [];
    protected static $builtinExclusions = [
        ['pattern' => 'wp\\-includes\\/version\\.php', 'include' => self::EXCLUSION_PATTERNS_KNOWN_FILES],
        //Excluded from the known files scan because non-en_US installations will have extra content that fails the check, still in malware scan
        ['pattern' => '(?:wp\\-includes|wp\\-admin)\\/(?:[^\\/]+\\/+)*(?:\\.htaccess|\\.htpasswd|php_errorlog|error_log|[^\\/]+?\\.log|\\._|\\.DS_Store|\\.listing|dwsync\\.xml)', 'include' => self::EXCLUSION_PATTERNS_KNOWN_FILES],
    ];
    protected $scanEngine;
    public function __construct($path, $scanEngine)
    {
        if ('/' != $path[\strlen($path) - 1]) {
            $path .= '/';
        }
        $this->path = $path;
        $this->scanEngine = $scanEngine;
        $this->patternsMax = Signature::max('sig_id');
        $this->results = [];
        $this->errorMsg = \false;
        $this->patterns = [];
        $this->setupSigs();
    }
    protected function setupSigs()
    {
        $sigs = Signature::query()->get();
        $patterns = [];
        foreach ($sigs as $sig) {
            $pattern = $sig->pattern;
            if (\false === @\preg_match('/' . $pattern . '/iS', null)) {
                StatusHelper::add(1, 'error', 'A regex from DB is invalid. The pattern is: ' . \htmlspecialchars($pattern));
            } else {
                $patterns[$sig->sig_id] = $sig;
            }
        }
        $this->patterns['rules'] = $patterns;
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
    public function scan($engine)
    {
        \set_error_handler(array(self::class, 'error_handler'), \E_RECOVERABLE_ERROR);
        $this->scanEngine = $engine;
        $knownFiles = [];
        foreach (KnownFile::query()->get() as $key => $kFile) {
            $knownFiles[$kFile->path] = $kFile;
        }
        $this->knownFiles = $knownFiles;
        if (!$this->startTime) {
            $this->startTime = \microtime(\true);
        }
        $this->lastStatusTime = (float) ConfigHelper::get('fileScannerlastStatusTime', 0);
        if (!$this->lastStatusTime) {
            $this->lastStatusTime = \microtime(\true);
            ConfigHelper::set('fileScannerlastStatusTime', $this->lastStatusTime);
        }
        $this->backtrackLimit = $this->setBacktrackLimit();
        /* File Scanner */
        $lastCount = 'whatever';
        $remainingFileCount = MalwareHelper::countRemaining();
        StatusHelper::add(1, 'info', 'Files to be scanned: ' . $remainingFileCount);
        if (0 === ConfigHelper::get('totalFiles', 0)) {
            ConfigHelper::set('totalFiles', $remainingFileCount);
        }
        ConfigHelper::set('remainingFiles', $remainingFileCount);
        /*if ($thisCount == $lastCount) {
              //count should always be decreasing. If not, we're in an infinite loop so lets catch it early
              StatusHelper::add(4, 'info', 'Detected loop in malware scan, aborting.');
              break;
          }
          $lastCount = $thisCount;*/
        $files = MalwareHelper::files(200);
        if (\count($files) < 1) {
            StatusHelper::add(4, 'info', 'No files remaining for malware scan');
            return \true;
        }
        foreach ($files as $record) {
            $this->processFile($record);
        }
        /* End of File Scanner */
    }
    private function processFile($record)
    {
        $file = $record->filePath;
        //sleep(1);
        //StatusHelper::add('10', 'error', $file);
        if (!\file_exists($this->path . $file)) {
            MalwareHelper::markComplete($record, 0);
            return;
        }
        $fileSum = $record->newMD5;
        $fileExt = '';
        if (\preg_match('/\\.([a-zA-Z\\d\\-]{1,7})$/', $file, $matches)) {
            $fileExt = \strtolower($matches[1]);
        }
        $isPHP = \false;
        if (\preg_match('/\\.(?:php(?:\\d+)?|phtml)(\\.|$)/i', $file)) {
            $isPHP = \true;
        }
        $isHTML = \false;
        if (\preg_match('/\\.(?:html?)(\\.|$)/i', $file)) {
            $isHTML = \true;
        }
        $isJS = \false;
        if (\preg_match('/\\.(?:js|svg)(\\.|$)/i', $file)) {
            $isJS = \true;
        }
        //if(strpos($file, 'footer.php') == false){
        //MalwareHelper::markComplete($record);
        //return;
        //}
        $isScanImagesFile = \false;
        if (!$isPHP && \preg_match('/^(?:jpg|jpeg|mp3|avi|m4v|mov|mp4|gif|png|tiff?|svg|sql|js|tbz2?|bz2?|xz|zip|tgz|gz|tar|log|err\\d+)$/', $fileExt)) {
            if (!$isJS) {
                MalwareHelper::markComplete($record);
                return;
            }
        }
        $isHighSensitivityFile = \false;
        if ('sql' == \strtolower($fileExt)) {
            MalwareHelper::markComplete($record);
            return;
        }
        if (ServerHelper::fileTooBig($this->path . $file)) {
            StatusHelper::add(2, 'error', \sprintf('Encountered file that is too large: %s - Skipping.', $file));
            MalwareHelper::markComplete($record);
            return;
        }
        $ignoredList = $this->scanEngine->getIgnoredPathsAndChecksums();
        if (isset($ignoredList['ignoredFiles']) && \is_array($ignoredList['ignoredFiles'])) {
            if (\array_search($fileSum, \array_values($ignoredList['ignoredFiles'])) !== \false) {
                $realChecksum = \md5_file($this->path . $file);
                StatusHelper::add(2, 'file-ignored in scanner', \sprintf('File checksum found in scanner options ignoredFiles : %s - Skipping from file. %s - with filechecksum(db) and also real checksum - %s', $file, $fileSum, $realChecksum));
                MalwareHelper::markComplete($record);
                return;
            }
        }
        $filePathMd5 = \md5($this->path . $file);
        if (isset($ignoredList['ignoredPaths']) && \is_array($ignoredList['ignoredPaths'])) {
            if (\array_search($filePathMd5, \array_values($ignoredList['ignoredPaths'])) !== \false) {
                StatusHelper::add(2, 'file-ignored in scanner', \sprintf('File checksum found in scanner options ignoredPaths : %s - Skipping from file.', $file));
                MalwareHelper::markComplete($record);
                return;
            }
        }
        // We have begun file scanning
        $fsize = @\filesize($this->path . $file);
        //Checked if too big above
        $fsize = ServerHelper::formatBytes($fsize);
        /*
        if (function_exists('memory_get_usage')) {
            StatusHelper::add(4, 'info', sprintf('Scanning contents: %s (Size: %s Mem: %s)', $file, $fsize, ServerHelper::formatBytes(memory_get_usage(true))));
        } else {
            StatusHelper::add(4, 'info', sprintf('Scanning contents: %s (Size: %s)', $file, $fsize));
        }
        */
        $stime = \microtime(\true);
        $fh = @\fopen($this->path . $file, 'r');
        if (!$fh) {
            MalwareHelper::markComplete($record);
            return;
        }
        $totalRead = 0;
        $regexMatchedGlobal = \false;
        while (!\feof($fh)) {
            $data = \fread($fh, 1 * 1024 * 1024);
            //read 1 megs max per chunk
            $readSize = ServerHelper::strlen($data);
            $currentPosition = $totalRead;
            $totalRead += $readSize;
            if ($readSize < 1) {
                break;
            }
            $extraMsg = '';
            $treatAsBinary = $isPHP || $isHTML;
            $regexMatched = \false;
            $stoppedOnSignature = (int) $record->stoppedOnSignature;
            $resumedOnSignature = null;
            foreach ($this->patterns['rules'] as $rule) {
                $stoppedOnSignature = $record->stoppedOnSignature;
                if ($stoppedOnSignature == $this->patternsMax) {
                    break;
                }
                if (!empty($stoppedOnSignature) && 0 !== $stoppedOnSignature && 'n' !== $record->isSafeFile) {
                    //Advance until we find the rule we stopped on last time
                    if ($rule->sig_id !== $stoppedOnSignature && \is_null($resumedOnSignature)) {
                        continue;
                    } else {
                        if (\is_null($resumedOnSignature)) {
                            $resumedOnSignature = $rule->sig_id;
                            StatusHelper::add(4, 'info', \sprintf('Resuming malware scan at rule %s.', $rule->sig_id));
                            continue;
                        }
                        $resumedOnSignature = $rule->sig_id;
                    }
                }
                $type = isset($rule->scanType) && !empty($rule->scanType) ? $rule->scanType : 'server';
                $logOnly = isset($rule->logOnly) && !empty($rule->logOnly) ? $rule->logOnly : \false;
                //$commonStringIndexes = (isset($rule[8]) && is_array($rule[8])) ? $rule[8] : array();
                $customMessage = isset($rule->name) ? 'The file was flagged as <strong>' . $rule->name . '</strong> and appears to be created by a hacker with malicious intent. If you know about this file you can choose to ignore it to exclude it from future scans.' : 'This file appears to be created by hacker to perform malicious activity. If you know about this file you can choose to ignore it to exclude it from future scans.';
                if ('server' == $type && !$treatAsBinary) {
                    continue;
                } elseif (('both' == $type || 'browser' == $type) && $isJS) {
                    $extraMsg = '';
                } elseif (('both' == $type || 'browser' == $type) && !$treatAsBinary) {
                    continue;
                }
                if (\preg_match('/(' . $rule->pattern . ')/iS', $data, $matches, \PREG_OFFSET_CAPTURE)) {
                    $matchString = $matches[1][0];
                    $matchOffset = $matches[1][1];
                    $beforeString = ServerHelper::substr($data, \max(0, $matchOffset - 100), $matchOffset - \max(0, $matchOffset - 100));
                    $afterString = ServerHelper::substr($data, $matchOffset + \strlen($matchString), 100);
                    if (!$logOnly) {
                        //MalwareHelper::markUnsafe($record);
                        $dataForFile = $this->dataForFile($file);
                        $this->addResult(['type' => 'file', 'severity' => 'critical', 'path' => $this->path . $file, 'ignorePath' => \md5($this->path . $file), 'ignoreChecksum' => $fileSum, 'shortMsg' => 'Possible malware: ' . \htmlspecialchars($file), 'longMsg' => $customMessage . ' ' . '<br/><br/>The malicious text in this file is:' . ' ' . '<br/><code>' . ServerHelper::potentialBinaryStringToHTML(ServerHelper::strlen($matchString) > 200 ? ServerHelper::substr($matchString, 0, 200) . '...' : $matchString) . '</code>' . '<br><br>' . \sprintf('<strong>Issue type:</strong> %s', \htmlspecialchars($rule->category)) . '<br>' . \sprintf('<strong>Description: </strong> %s', \htmlspecialchars($rule->description)) . $extraMsg, 'data' => \array_merge(['file' => $file, 'ruleName' => isset($rule->name) ? $rule->name : null, 'maliciousText' => ServerHelper::potentialBinaryStringToHTML(ServerHelper::strlen($matchString) > 200 ? ServerHelper::substr($matchString, 0, 200) . '...' : $matchString), 'issueType' => \htmlspecialchars($rule->category), 'issueDescription' => \htmlspecialchars($rule->description), 'extraMsg' => $extraMsg], $dataForFile)]);
                        StatusHelper::add('10', 'malware', 'Malware - ' . $file);
                        MalwareHelper::markCompleteDetection($record, $rule->sig_id);
                    }
                    $regexMatched = \true;
                    $regexMatchedGlobal = \true;
                    //$this->scanEngine->recordMetric('malwareSignature', $rule[0], array('file' => $file, 'match' => $matchString, 'before' => $beforeString, 'after' => $afterString, 'md5' => $record->newMD5, 'shac' => $record->SHAC), false);
                    break;
                }
                if ($this->scanEngine->shouldFork()) {
                    MalwareHelper::updateStoppedOn($record, $rule->sig_id);
                    \fclose($fh);
                    StatusHelper::add(4, 'info', \sprintf('Forking during malware scan (%s) to ensure continuity.', $rule->sig_id));
                    $this->scanEngine->fork();
                    //exits
                }
            }
            if ($regexMatched) {
                break;
            }
            if ($totalRead > 2 * 1024 * 1024) {
                break;
            }
        }
        \fclose($fh);
        ++$this->totalFilesScanned;
        if (\microtime(\true) - $this->lastStatusTime > 1) {
            $this->lastStatusTime = \microtime(\true);
            $this->writeScanningStatus();
        }
        $isSafeFile = '';
        if ('n' == $record->isSafeFile && !$regexMatchedGlobal) {
            // Mark a previously flagged file as safe if it no longer matches a malware signature
            $isSafeFile = 'y';
        }
        MalwareHelper::markComplete($record, $this->patternsMax, $isSafeFile);
        $this->scanEngine->forkIfNeeded();
        if (\false !== $this->backtrackLimit) {
            \ini_set('pcre.backtrack_limit', $this->backtrackLimit);
        }
        return $this->results;
    }
    protected function writeScanningStatus()
    {
        $rate = $this->totalFilesScanned / (\microtime(\true) - $this->startTime);
        StatusHelper::add(2, 'info', \sprintf('Scanned contents of %d additional files at %.2f per second', $this->totalFilesScanned, $rate));
        ConfigHelper::set('fileRate', $rate);
    }
    protected function addResult($result)
    {
        for ($i = 0; $i < \sizeof($this->results); ++$i) {
            if ('file' == $this->results[$i]['type'] && $this->results[$i]['data']['file'] == $result['data']['file']) {
                if ($this->results[$i]['severity'] > $result['severity']) {
                    $this->results[$i] = $result;
                    //Overwrite with more severe results
                }
                return;
            }
        }
        $deleteKey = CommonHelper::bigRandomHex();
        Issue::addIssue($result['type'], $result['severity'], $result['path'], $result['ignorePath'], $result['ignoreChecksum'], $result['shortMsg'], $deleteKey, $result['longMsg'], $result['data'], \true);
        //We don't have a results for this file so append
        $this->results[] = $result;
    }
    private function setBacktrackLimit()
    {
        $backtrackLimit = \ini_get('pcre.backtrack_limit');
        if (\is_numeric($backtrackLimit)) {
            $backtrackLimit = (int) $backtrackLimit;
            if ($backtrackLimit > 10000000) {
                \ini_set('pcre.backtrack_limit', 1000000);
                StatusHelper::add(4, 'info', \sprintf('Backtrack limit is %d, reducing to 1000000', $backtrackLimit));
            }
        } else {
            $backtrackLimit = \false;
        }
        return $backtrackLimit;
    }
    private function dataForFile($file, $fullPath = null)
    {
        $data = [];
        $isKnownFile = \false;
        if (\array_key_exists($file, $this->knownFiles)) {
            $isKnownFile = \true;
            $data['cType'] = 'core';
        }
        $data['canDiff'] = $isKnownFile;
        $data['canFix'] = $isKnownFile;
        $data['canDelete'] = !$isKnownFile;
        return $data;
    }
}
