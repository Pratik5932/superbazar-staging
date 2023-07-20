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
use AstraPrefixed\GetAstra\Plugins\Scanner\Models\FileMods;
use AstraPrefixed\GetAstra\Plugins\Scanner\Models\IndexedFile;
use AstraPrefixed\GetAstra\Plugins\Scanner\Models\Issue;
use AstraPrefixed\GetAstra\Plugins\Scanner\Models\KnownFile;
use AstraPrefixed\GetAstra\Plugins\Scanner\Models\Signature;
use AstraPrefixed\Symfony\Component\Finder\Finder;
class HasherService
{
    private $engine = \false;
    private $startTime = \false;
    public $striplen = 0;
    public $totalFiles = 0;
    public $totalDirs = 0;
    public $totalData = 0;
    public $stoppedOnFile = \false;
    private $scanUnknownCMSFiles = \false;
    private $knownFiles = \false;
    private $filesMods = \false;
    private $malwareData = '';
    private $coreHashesData = '';
    private $haveIssues = [];
    private $status = [];
    private $path = \false;
    private $filesToScan = [];
    private $indexed = \false;
    private $indexSize = 0;
    private $currentIndex = 0;
    private $patternsMax = 0;
    public function __construct($striplen, $path, $engine)
    {
        $this->striplen = $striplen;
        $this->path = $path;
        $this->filesToScan = ConfigHelper::get('filesToScan', []);
        $this->engine = $engine;
        $this->indexSize = (int) ConfigHelper::get('indexSize', 0);
        $this->currentIndex = (int) ConfigHelper::get('currentIndex', 0);
        $this->startTime = \microtime(\true);
        if (ConfigHelper::get('scanUnknownCMSFiles', \true)) {
            $this->scanUnknownCMSFiles = \true;
        }
        if ('/' != $this->path[\strlen($this->path) - 1]) {
            $this->path .= '/';
        }
        if (!\is_readable($path)) {
            throw new \Exception('Could not read directory ' . $this->path . ' to do scan.');
        }
        \set_error_handler(array(self::class, 'error_handler'), \E_RECOVERABLE_ERROR);
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
    public function run($engine)
    {
        $this->engine = $engine;
        $files = \array_keys($this->filesToScan);
        $indexedFiles = [];
        foreach ($files as $file) {
            if ('.' == $file || '..' == $file) {
                continue;
            }
            $filePath = $this->path . $file;
            $this->folderIndex($filePath, $indexedFiles);
            unset($this->filesToScan[$file]);
            ConfigHelper::set('filesToScan', $this->filesToScan);
            if ($this->engine->shouldFork()) {
                $this->engine->fork();
            }
        }
        $this->_serviceIndexQueue($indexedFiles, \true);
        ConfigHelper::set('totalFilesScanned', IndexedFile::count());
        return \true;
    }
    public function folderIndex($path, &$indexedFiles)
    {
        if ('/..' == \substr($path, -3, 3) || '/.' == \substr($path, -2, 2)) {
            return;
        }
        if (!\is_readable($path)) {
            StatusHelper::add(1, 'error', 'Path not readable: ' . $path);
            return;
        }
        if (\is_dir($path)) {
            $finder = new Finder();
            $finder->files()->ignoreUnreadableDirs()->exclude(['cache', 'storage', 'astra'])->ignoreDotFiles(\true)->in($path)->name('/\\.(php\\d?|phtml|html|js|svg|suspected|sql|7z|rar|tar|zip|tgz|tbz?|bz2?|css|scss|less|pcss|gz|bz2?)$/')->size('< 10M');
            foreach ($finder as $file) {
                $relativeFile = \substr($file->getRealPath(), $this->striplen);
                if ($this->_shouldProcessFile($file->getRealPath())) {
                    $indexedFiles[] = ['path' => $relativeFile, 'md5' => \md5_file($file->getRealPath())];
                }
            }
        } else {
            if (\is_file($path)) {
                $relativeFile = \substr($path, $this->striplen);
                if ($this->_shouldProcessFile($path)) {
                    $indexedFiles[] = ['path' => $relativeFile, 'md5' => \md5_file($path)];
                }
            }
        }
        $this->_serviceIndexQueue($indexedFiles);
    }
    private function _shouldProcessFile($path = '')
    {
        if (!\is_readable($path)) {
            return \false;
        }
        return \true;
    }
    private function _serviceIndexQueue(&$indexedFiles, $final = \false)
    {
        $payload = [];
        if (\count($indexedFiles) > 499 || $final) {
            $payload = \array_chunk($indexedFiles, 499);
        }
        if (\count($payload) > 0) {
            foreach ($payload as $chunk) {
                IndexedFile::insert($chunk);
                //sleep(1);
            }
            $this->indexSize += \count($indexedFiles);
            ConfigHelper::set('indexSize', $this->indexSize);
            $indexedFiles = [];
            StatusHelper::add(2, 'info', "{$this->indexSize} files indexed");
        }
    }
    public function passiveScan($engine)
    {
        $this->engine = $engine;
        $this->patternsMax = Signature::max('sig_id');
        // Parse the knownFiles
        $knownFiles = [];
        foreach (KnownFile::query()->get() as $key => $kFile) {
            $knownFiles[$kFile->path] = $kFile;
        }
        $this->knownFiles = $knownFiles;
        // Parse the lastScanFiles
        $fileMods = [];
        $t = FileMods::query()->get();
        StatusHelper::add(2, 'info', \count($t) . ' files from previous scan');
        foreach ($t as $key => $mFile) {
            $fileMods[$mFile->filePath] = $mFile;
        }
        $this->filesMods = $fileMods;
        $remaining = IndexedFile::query()->count();
        if ($remaining > 0) {
            $this->analyzeFiles();
        }
        return \true;
    }
    private function analyzeFiles()
    {
        $filesForScan = IndexedFile::query()->get()->take(200);
        $newFiles = [];
        $filesProcessed = [];
        foreach ($filesForScan as $num => $file) {
            $this->processFile($file, $newFiles);
            $filesProcessed[] = $file->path;
            if ($num % 3) {
                if ($this->engine->shouldFork()) {
                    $this->updateKnownFileRecords($newFiles, $filesProcessed);
                    $this->engine->fork();
                }
            }
        }
        $this->updateKnownFileRecords($newFiles, $filesProcessed);
        //TODO Delete files if necessary
        if ($this->engine->shouldFork()) {
            $this->engine->fork();
        }
        $remaining = IndexedFile::query()->count();
        if ($remaining > 0) {
            $this->analyzeFiles();
        }
    }
    private function updateKnownFileRecords(&$newFiles, &$filesProcessed)
    {
        try {
            $newFilesChunks = \array_chunk($newFiles, 124);
            foreach ($newFilesChunks as $chunk) {
                FileMods::insert($chunk);
            }
        } catch (\Exception $e) {
            StatusHelper::add(10, 'error', $e->getMessage());
        }
        //StatusHelper::add(2, 'info', 'Delete paths:' . json_encode($filesProcessed));
        IndexedFile::query()->whereIn('path', $filesProcessed)->delete();
    }
    private function processFile($file, &$newFiles)
    {
        $pathFile = $file->path;
        $realFile = $this->path . $file->path;
        if (ServerHelper::fileTooBig($realFile)) {
            StatusHelper::add(4, 'info', "Skipping file larger than max size: {$realFile}");
            return;
        }
        $ignoredList = $this->engine->getIgnoredPathsAndChecksums();
        // $fp = fopen(ASTRAROOT.'vardump.txt', 'w');
        // fwrite($fp, json_encode($this->engine->getIgnoredPathsAndChecksums()));
        // fclose($fp);
        // die();
        $fileChecksum = \md5('coreModified' . $pathFile . $file->md5);
        $filePathMd5 = \md5('coreModified' . $pathFile);
        if (isset($ignoredList['ignoredFiles']) && \is_array($ignoredList['ignoredFiles'])) {
            if (\array_search($fileChecksum, \array_values($ignoredList['ignoredFiles'])) !== \false) {
                StatusHelper::add(2, 'file-ignored in hasher', \sprintf('File checksum found in scanner options ignoredFiles : %s - Skipping from hasher.', $realFile));
                return;
            }
        }
        if (isset($ignoredList['ignoredPaths']) && \is_array($ignoredList['ignoredPaths'])) {
            if (\array_search($filePathMd5, \array_values($ignoredList['ignoredPaths'])) !== \false) {
                StatusHelper::add(2, 'file-ignored in hasher', \sprintf('File checksum found in scanner options ignoredPaths : %s - Skipping from hasher.', $realFile));
                return;
            }
        }
        /*
        if (function_exists('memory_get_usage')) {
            StatusHelper::add(4, 'info', "Scanning: $realFile (Mem:" . sprintf('%.1f', memory_get_usage(true) / (1024 * 1024)) . "M)");
        } else {
            StatusHelper::add(4, 'info', "Scanning: $realFile");
        }
        */
        //TODO Exit if the file was ignored
        $isPreviouslyIndexed = \array_key_exists($pathFile, $this->filesMods);
        $isKnownFile = \array_key_exists($pathFile, $this->knownFiles);
        $pFile = null;
        if ($isPreviouslyIndexed) {
            $pFile = $this->filesMods[$pathFile];
        }
        $kFile = null;
        if ($isKnownFile) {
            $kFile = $this->knownFiles[$pathFile];
        }
        if (\preg_match('/\\.suspected$/i', $pathFile)) {
            //Already iterating over all files in the search areas so generate this list here
            StatusHelper::add(4, 'info', "Found .suspected file: {$pathFile}");
            Issue::addIssue('suspected', 'high', $realFile, \md5('suspected' . $pathFile), \md5('suspected' . $pathFile . $file->md5), 'File with .suspected extension found: ' . $pathFile, '', 'This file contains the .suspected extension and is likely to have contained malicious code. ', ['file' => $pathFile, 'cType' => 'core', 'canDiff' => \true, 'canFix' => \true, 'canDelete' => \false, 'haveIssues' => 'core'], \true);
        }
        if (\preg_match('/^[^\\/]+\\.(?:sql|7z|rar|tar|zip|tgz|tbz?|bz2?)/i', $pathFile)) {
            StatusHelper::add(4, 'info', "Found sql or archive file: {$pathFile}");
            $deleteKey = CommonHelper::bigRandomHex();
            Issue::addIssue('sensitive', 'medium', $realFile, \md5('archive' . $pathFile), \md5('archive' . $pathFile . $file->md5), 'File possibly contains sensitive information: ' . $pathFile, $deleteKey, 'This file is a database backup or archive that may contain sensitive information detrimental to security if publicly accessible.', ['file' => $pathFile, 'cType' => 'sensitive', 'canDiff' => \false, 'canFix' => \false, 'canDelete' => \true, 'haveIssues' => 'sensitive'], \false);
        }
        if (!$isPreviouslyIndexed && !$isKnownFile) {
            $newFiles[] = ['filenameMD5' => \md5($pathFile), 'filePath' => $pathFile, 'oldMD5' => 'whatever', 'newMD5' => $file->md5, 'stoppedOnSignature' => 0, 'knownFile' => 0, 'isSafeFile' => '?', 'toScan' => \true];
            return \true;
        }
        if ($isPreviouslyIndexed && $pFile->stoppedOnSignature < $this->patternsMax) {
            // New Malware Signatures have released since last file scan
            if (!$isKnownFile) {
                MalwareHelper::updateChecksum($pFile->filenameMD5, $pFile->newMD5, $file->md5, \true);
            }
        }
        if ($isPreviouslyIndexed && $pFile->newMD5 !== $file->md5) {
            // The file has changed since last scan
            if (!$isKnownFile) {
                // Case for known files will be dealt ahead
                MalwareHelper::updateChecksum($pFile->filenameMD5, $pFile->newMD5, $file->md5, \true);
            }
        }
        if ($isPreviouslyIndexed && 'n' == $pFile->isSafeFile) {
            // The file was previously flagged as malware
            MalwareHelper::updateChecksum($pFile->filenameMD5, $pFile->oldMD5, $pFile->newMD5, \true);
        }
        if ($isKnownFile && $file->md5 !== $kFile->md5) {
            // The file is a knownFile but not the same as it should be
            if ($isPreviouslyIndexed) {
                MalwareHelper::updateChecksum($pFile->filenameMD5, 'blank', $file->md5, \true);
            } else {
                // Since this is a core which was not indexed earlier, add it to the index
                $newFiles[] = ['filenameMD5' => \md5($pathFile), 'filePath' => $pathFile, 'oldMD5' => $kFile->md5, 'newMD5' => $file->md5, 'stoppedOnSignature' => 0, 'knownFile' => 1, 'isSafeFile' => '?', 'toScan' => \true];
            }
            $isExcludedFile = \false;
            if (\preg_match('/\\.(?:info|txt|css|scss)(\\.|$)/i', $pathFile)) {
                $isExcludedFile = \true;
            }
            if (!$isExcludedFile) {
                $cms = ConfigHelper::get('cms', 'php');
                if (!\in_array($cms, ['magento', 'opencart'])) {
                    //StatusHelper::add(2, 'debug-party', 'path77 -'. $realFile .'with hash filepathmd77 -'.$filePathMd5.' and filechecksum -'.$fileChecksum. ', scanner options -' .json_encode($ignoredList));
                    Issue::addIssue('known_file', 'medium', $realFile, \md5('coreModified' . $pathFile), \md5('coreModified' . $pathFile . $file->md5), 'Core CMS file modified: ' . $pathFile, '', 'This CMS core file has been modified and differs from the original file distributed with this version of the CMS.', ['file' => $pathFile, 'cType' => 'core', 'canDiff' => \true, 'canFix' => \true, 'canDelete' => \false, 'haveIssues' => 'core'], \true);
                }
            }
        }
        if ($isKnownFile && $file->md5 == $kFile->md5 && !$isPreviouslyIndexed) {
            // The file is a known file and safe, but not previously indexed
            $newFiles[] = ['filenameMD5' => \md5($pathFile), 'filePath' => $pathFile, 'oldMD5' => $file->md5, 'newMD5' => $file->md5, 'stoppedOnSignature' => 0, 'knownFile' => 1, 'isSafeFile' => 'y', 'toScan' => \false];
        }
    }
}
