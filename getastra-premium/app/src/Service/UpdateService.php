<?php

namespace AstraPrefixed\GetAstra\Client\Service;

use AstraPrefixed\GetAstra\Client\Helper\IpBlockingHelper;
use AstraPrefixed\GetAstra\Client\Tclient\ClientApi;
use AstraPrefixed\GetAstra\Client\Tclient\Configuration;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Log\LoggerInterface;
use AstraPrefixed\Psr\SimpleCache\CacheInterface;
use AstraPrefixed\splitbrain\PHPArchive\Tar;
use AstraPrefixed\GetAstra\Client\Controller\Update\UpdateController;
use AstraPrefixed\GetAstra\Client\Helper\Cms\AbstractCmsHelper;
use AstraPrefixed\GetAstra\Client\Tclient\GatekeeperUpdateJob\GatekeeperUpdateJobApi;
use AstraPrefixed\GetAstra\Client\Tclient\SiteApi;
/**
 * Service to help update Gatekeeper itself.
 */
class UpdateService
{
    /**
     * @var ClientApi|null
     */
    private $updateApi;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var CacheInterface
     */
    private $options;
    private $oauthService;
    private $apiUrl;
    /**
     * @var GatekeeperUpdateJobApi
     */
    private $gkUpdateJobApi;
    /**
     * @var SiteApi
     */
    private $siteApi;
    private const UPDATE_ERROR_LOG_FILE = ASTRAROOT . 'updateModuleErrors.txt';
    private const UPDATE_SUCCESS_LOG_FILE = ASTRAROOT . 'updateSuccess.txt';
    private const UPDATE_LOCK_FILE = ASTRAROOT . '.updateLock';
    private const UPDATER_FINAL_SCRIPT_NAME = 'astraUpdateModule.php';
    private $updateExtractDir;
    public const GK_UPDATE_JOB_ID_OPTIONS_KEY = 'gkUpdateJobId';
    private $cmsSlug, $extension;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->options = $container->get('options');
        $this->oauthService = $container->get('oauth');
        $this->logger = $container->get('logger');
        $this->apiUrl = \substr($container->get('settings')['relay']['api_url_https'], 0, -1);
        $this->updateExtractDir = \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'astraVar' . \DIRECTORY_SEPARATOR . 'extract_updater';
        //$this->updateExtractDir = ASTRAROOT.'test_extract'.DIRECTORY_SEPARATOR;
        // $cms = $this->options->get('fullSiteObject')['cms'];
        // if (\strpos($cms, 'wordpress') !== \false) {
        //     $this->cmsSlug = 'gatekeeper';
        //     $this->extension = '.tar';
        // } else {
        //     $this->cmsSlug = $cms;
        //     $this->extension = '.zip';
        // }
        // $this->siteSettingsService = $container->get('siteSettings');
        // $this->cmsSlug = $this->options->get($this->siteSettingsService::FULL_SITE_OBJECT_KEY);
        //hardcoded cmsSlug, extension for now.
        $this->cmsSlug = 'wordpress';
        $this->extension = 'tar';
    }
    public function checkForUpdate()
    {
        $this->initializeApis();
        if (!$this->updateApi) {
            $this->logger->error('Failed to check server for updates (no token)');
            return null;
        }
        try {
            $res = $this->updateApi->getClientVersions();
        } catch (\Exception $e) {
            $this->logger->error('Failed to check server for updates (UpdateApi unavailable)');
            return null;
        }
        $apiVersions = \json_decode($res, \true);
        //$versionsAvailableForCms = $apiVersions[$this->cmsSlug];
        $latestVersion = \array_filter($apiVersions, function ($var) {
            return $var['latest'] == \true;
        });
        foreach ($latestVersion as $key => $val) {
            $latestVersionNo = $key;
            break;
        }
        if (-1 === \version_compare(GATEKEEPER_VERSION, $latestVersionNo)) {
            return $latestVersionNo;
        } else {
            return null;
        }
    }
    public function getUpdateExtractDir()
    {
        return $this->updateExtractDir;
    }
    public function isUpdateExtractDirWritable()
    {
        if (!\is_dir($this->updateExtractDir)) {
            if (!\mkdir($this->updateExtractDir, 0775, \true)) {
                return \false;
            }
        }
        if (!\is_writable(\dirname($this->updateExtractDir))) {
            return \false;
        }
        return \true;
    }
    /**
     * metadata here is just scalar value - $gkUpdateJobId
     */
    public function handleGkUpdateTask($metaData)
    {
        $time = (new \DateTime())->format('c');
        $gkUpdateJobId = $metaData;
        if ($this->isUpdating()) {
            return ['result' => \false, 'reason' => 'Already updating, i.e Update handler already called for the given tasks.', 'time' => $time];
        }
        $this->createUpdateLock();
        $this->initializeApis();
        if (!$this->gkUpdateJobApi || !$this->updateApi) {
            $message = 'oAuth token not found in 1st step of updater module.';
            $this->logger->error($message);
            $this->removeUpdateLock();
            return ['result' => \false, 'reason' => $message, 'time' => $time];
        }
        //exiting if no update available
        $newVersion = $this->checkForUpdate();
        if (!$newVersion) {
            $message2 = 'No update available for Gatekeeper';
            $dataForPatch = \json_encode(\array_merge($this->prepareGkUpdateJobPatchData(), ['status' => 'completed', 'statusDescription' => $message2, 'oldVersion' => GATEKEEPER_VERSION]));
            try {
                $this->gkUpdateJobApi->patchGatekeeperUpdateJobItem($gkUpdateJobId, $dataForPatch);
                $this->logger->error($message2);
                $this->removeUpdateLock();
                return ['result' => \false, 'reason' => $message2, 'time' => $time];
            } catch (\Exception $e) {
                $this->logger->error('No updates available for GK, but could not notify Symfony about the same.');
                $this->removeUpdateLock();
                return ['result' => \false, 'reason' => 'No updates available for GK, but could not notify Symfony about the same.', 'time' => $time];
            }
        }
        //Install update (donwload, unzip)
        $res = $this->installUpdate($newVersion);
        $this->logger->error($res['errorMessage']);
        //Publish logs to symfony
        $status = $res['error'] == \false ? 'running' : 'failed';
        $dataForPatch = \json_encode(\array_merge($this->prepareGkUpdateJobPatchData(), ['status' => $status, 'statusDescription' => $res['errorMessage'], 'oldVersion' => GATEKEEPER_VERSION]));
        try {
            $this->gkUpdateJobApi->patchGatekeeperUpdateJobItem($gkUpdateJobId, $dataForPatch);
        } catch (\Exception $e) {
            //catching exception is important otherwise this fatal error will stop customer's site from working.
            $extraMessage = "GK could not patch updater module status={$status} logs to symfony for first part of update process status.";
            $this->logger->error($extraMessage);
        }
        //Final response
        if ($res['error'] == \false) {
            //SUCCESS
            $rm = ['result' => \true, 'reason' => 'First part of update is completed', 'time' => $time];
            if (isset($extraMessage)) {
                $rm['extraDebug'] = $extraMessage;
            }
            $this->removeUpdateLock();
            \file_put_contents(ASTRAROOT . '.update2', $time);
            //put .success flag. will call the the second step - individual script
            return $rm;
        } else {
            //FAILED
            $rm = ['result' => \false, 'reason' => $res['errorMessage'], 'time' => $time];
            if (isset($extraMessage)) {
                $rm['extraDebug'] = $extraMessage;
            }
            $this->removeUpdateLock();
            return $rm;
        }
    }
    /**
     * Helper function for the controller to undertake the first part of update process.
     * removal of old files, and new files being written to root dir happens in astraUpdateModule.php
     */
    private function installUpdate(string $version)
    {
        $this->initializeApis();
        try {
            $updateFile = $this->updateApi->getClientUpdate(['version' => $version, 'cms' => $this->cmsSlug, 'extension' => $this->extension]);
            $apiVersions = \json_decode($this->updateApi->getClientVersions(), \true);
        } catch (\Exception $e) {
            return [
                'error' => \true,
                //'errorMessage' => 'GK could not fetch the new updated package/details from symfony.'
                'errorMessage' => 'Astra plugin could not fetch the new version of the plugin.',
            ];
        }
        //this/latest version no.
        $thisVersion = $apiVersions[$version];
        //check if checksums match
        $md5 = \md5_file($updateFile->getPathname());
        if ($md5 !== $thisVersion['md5']) {
            return [
                'error' => \true,
                //'errorMessage' => 'Md5 hash for update files does not match'
                'errorMessage' => 'Plugin\'s new version files could not be verified whether they are from trusted source.',
            ];
        }
        //check if directory is writable
        $writeDir = $this->updateExtractDir;
        //@todo maybe change name of extract dir
        if (!\is_dir($writeDir)) {
            $bool = \mkdir($writeDir, 0775, \true);
            if (!$bool) {
                return [
                    'error' => \true,
                    //'errorMessage' => 'Unable to create directory for new GK zip'
                    'errorMessage' => 'Astra was unable to write files in the directory due to insufficient permissions.',
                ];
            }
        }
        if (!\is_writable(\dirname($writeDir))) {
            return ['error' => \true, 'errorMessage' => 'Astra was unable to write files in the directory due to insufficient permissions.'];
        }
        //unzip
        try {
            $updateTarFile = new Tar();
            $updateTarFile->open($updateFile->getPathname());
            $updateTarFile->extract($writeDir);
        } catch (\Exception $e) {
            return ['error' => \true, 'errorMessage' => 'Astra was unable to unzip the new plugin files.'];
        }
        //@todo-future - run & test migrations, if at all we'll use orm/migrations.
        //right now doctrine cache is being used so no need for this.
        //no phinx migrations used right now, so no need to re-run migrations
        return [
            'error' => \false,
            //'errorMessage' => 'First part of update process (update controller) complete successfully.'
            'errorMessage' => 'First stage of Astra plugin update process is complete successfully. New plugin will be ready soon.',
        ];
    }
    /**
     * After the final step of update has happened, i.e the astraUpdateModule.php file has finished running,
     * Slim posts the success/failure logs to symfony.
     * Function works when either error or success file log is present,
     * and is called from checksMiddleware.
     */
    public function patchUpdaterMessagesToSymfony() : void
    {
        if (!\file_exists(self::UPDATE_ERROR_LOG_FILE) && !\file_exists(self::UPDATE_SUCCESS_LOG_FILE)) {
            return;
            // if no logs present exit
        }
        $file = \file_exists(self::UPDATE_ERROR_LOG_FILE) ? self::UPDATE_ERROR_LOG_FILE : self::UPDATE_SUCCESS_LOG_FILE;
        $fileHash = \md5_file($file);
        // if logs present then check retry count threshold
        if ($this->options->has('retryCountForUpdaterMessages') && $this->options->get('retryCountForUpdaterMessages') !== null) {
            $threshold = 5;
            $current = $this->options->get('retryCountForUpdaterMessages')['count'];
            $fileHashCheck = $this->options->get('retryCountForUpdaterMessages')['fileHash'];
            if ($current > $threshold) {
                if ($fileHash === $fileHashCheck) {
                    return;
                    // threshold breached dont try to update messages
                } else {
                    // new log file found reset the counters & continue
                    $this->options->set('retryCountForUpdaterMessages', ['count' => 1, 'fileHash' => $fileHash]);
                }
            } else {
                $this->options->set('retryCountForUpdaterMessages', ['count' => ++$current, 'fileHash' => $fileHash]);
                // threshold not breached, continue to retry posting message
            }
        } else {
            $this->options->set('retryCountForUpdaterMessages', ['count' => 1, 'fileHash' => $fileHash]);
        }
        $this->initializeApis();
        if (!$this->gkUpdateJobApi) {
            $this->logger->error('Could not send GK update errors to Symfony due to uninitialized GkUpdateJobApi which could be due to missing token !');
            return;
        }
        $options = $this->container->get('options');
        $gkUpdateJobId = $options->get(self::GK_UPDATE_JOB_ID_OPTIONS_KEY);
        $siteId = $options->get('siteId');
        if (!$gkUpdateJobId) {
            $this->logger->error('Could not send GK update errors to Symfony due to missing GKUpdateJob ID !');
            return;
        }
        if (\file_exists(self::UPDATE_ERROR_LOG_FILE)) {
            //publishing GK updater error messages to symfony
            $data = \file_get_contents(self::UPDATE_ERROR_LOG_FILE);
            $dataForPatch = \json_encode(\array_merge($this->prepareGkUpdateJobPatchData(), ['status' => 'failed', 'statusDescription' => $data]));
            try {
                $this->gkUpdateJobApi->patchGatekeeperUpdateJobItem($gkUpdateJobId, $dataForPatch);
                \unlink(self::UPDATE_ERROR_LOG_FILE);
                $this->options->set('retryCountForUpdaterMessages', null);
            } catch (\Exception $e) {
                // catching exception is important otherwise this fatal error will stop customer's site from working.
                $this->logger->error('open-api(guzzle) client error while patching updater\'s error logs.');
                return;
            }
        }
        if (\file_exists(self::UPDATE_SUCCESS_LOG_FILE)) {
            //publishing GK updater success messages to symfony
            $data = \json_decode(\file_get_contents(self::UPDATE_SUCCESS_LOG_FILE), \true);
            $version = \trim(\str_replace(["\n", "\r"], '', $data['version']));
            //$version = rtrim($data['version']);
            $dataForPatch = \json_encode(\array_merge($this->prepareGkUpdateJobPatchData(), ['status' => 'completed', 'statusDescription' => $data['message'], 'newVersion' => $version]));
            $dataForPatch2 = \json_encode(['workerVersion' => $version]);
            try {
                $this->gkUpdateJobApi->patchGatekeeperUpdateJobItem($gkUpdateJobId, $dataForPatch);
                $this->siteApi->patchSiteItem($siteId, $dataForPatch2);
                \unlink(self::UPDATE_SUCCESS_LOG_FILE);
                \unlink(ASTRAROOT . self::UPDATER_FINAL_SCRIPT_NAME);
                \rename($this->updateExtractDir . \DIRECTORY_SEPARATOR . self::UPDATER_FINAL_SCRIPT_NAME, ASTRAROOT . self::UPDATER_FINAL_SCRIPT_NAME);
                $this->options->set('retryCountForUpdaterMessages', null);
            } catch (\Exception $e) {
                $this->logger->error('open-api(guzzle) client error while patching updater\'s success logs.');
                return;
            }
        }
        return;
    }
    /**
     * Helper function to prepare data to be patched to GKUpdateJob entity.
     * 
     * @return array $data
     */
    private function prepareGkUpdateJobPatchData() : ?array
    {
        $cmsAdapter = new AbstractCmsHelper();
        $cms = $cmsAdapter->getCms();
        $cmsName = empty($cms->getName()) ? null : $cms->getName();
        $cmsVersion = empty($cms->getVersion()) ? null : $cms->getVersion();
        $data = ['phpVersion' => \phpversion(), 'os' => \php_uname('s') . ' ' . \php_uname('v'), 'phpModules' => \json_encode(\get_loaded_extensions()), 'cms' => $cms->getName(), 'cmsVersion' => $cms->getVersion(), 'serverIp' => $_SERVER['SERVER_ADDR'], 'astraRootDir' => ASTRAROOT];
        return $data;
    }
    public function isUpdating() : bool
    {
        return \file_exists(self::UPDATE_LOCK_FILE);
    }
    private function removeUpdateLock()
    {
        if (\file_exists(self::UPDATE_LOCK_FILE)) {
            \unlink(self::UPDATE_LOCK_FILE);
        }
    }
    private function createUpdateLock()
    {
        \file_put_contents(self::UPDATE_LOCK_FILE, 'lockFile');
    }
    private function initializeApis()
    {
        $tokenObject = $this->oauthService->getTokenObject();
        $oauthClientId = $this->container->get('options')->get('oauthClientId');
        $oauthClientSecret = $this->container->get('options')->get('oauthClientSecret');
        if (isset($tokenObject, $oauthClientId, $oauthClientSecret, $this->apiUrl)) {
            $apiConfiguration = (new Configuration())->setAccessToken($tokenObject->getToken());
            $apiConfiguration->setHost($this->apiUrl)->setDebug(\false)->setUsername($oauthClientId)->setPassword($oauthClientSecret);
            if (!$this->gkUpdateJobApi) {
                $this->gkUpdateJobApi = new GatekeeperUpdateJobApi(null, $apiConfiguration);
            }
            if (!$this->siteApi) {
                $this->siteApi = new SiteApi(null, $apiConfiguration);
            }
            if (!$this->updateApi) {
                $this->updateApi = new ClientApi(null, $apiConfiguration);
            }
        } else {
            $this->logger->error('oAuth token not found in UpdateService, could not initialize client APIs.');
            $this->gkUpdateJobApi = null;
            $this->siteApi = null;
            $this->updateApi = null;
        }
    }
}
