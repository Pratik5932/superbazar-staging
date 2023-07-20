<?php

namespace AstraPrefixed;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
use AstraPrefixed\GetAstra\Client\Helper\IpBlockingHelper;
use AstraPrefixed\GetAstra\Client\Helper\UrlHelper;
use AstraPrefixed\GetAstra\Client\Helper\PluginInterface;
use AstraPrefixed\phpMussel\Core\Loader;
use AstraPrefixed\phpMussel\Core\Scanner;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Http\Message\ResponseInterface;
use AstraPrefixed\Psr\Http\Message\ServerRequestInterface;
use AstraPrefixed\Psr\Log\LoggerInterface;
/**
 * Description of UploadScannerPlugin.
 *
 * @author aditya
 */
class UploadScannerPlugin implements PluginInterface
{
    private $container;
    private $urlHelper;
    private $ipBlockingHelper;
    private $commonHelper;
    private $isEnabled;
    private $options;
    /**
     * @var LoggerInterface
     */
    private $logger;
    // private const MUSSLE_CACHE_PATH = ASTRAROOT.'var/phpmussel-cache/';
    // private const MUSSLE_QUARANTINE_PATH = ASTRAROOT.'var/phpmussel-quarantine/';
    // private const MUSSLE_SIGNATURE_PATH = ASTRAROOT.'var/phpmussel-signatures/';
    private const MUSSLE_CACHE_PATH = \ASTRA_STORAGE_ROOT . 'phpmussel-cache/';
    private const MUSSLE_QUARANTINE_PATH = \ASTRA_STORAGE_ROOT . 'phpmussel-quarantine/';
    private const MUSSLE_SIGNATURE_PATH = \ASTRA_STORAGE_ROOT . 'phpmussel-signatures/';
    public function getMigrationDirPath() : string
    {
        return '';
    }
    public function getName() : string
    {
        return 'UploadScannerPlugin';
    }
    public function getRoutes() : array
    {
        return '';
    }
    public function getVersion() : string
    {
        return 'v0.1';
    }
    public function isApiUser() : bool
    {
        return \true;
    }
    public function isRequestBlocker() : bool
    {
        return \true;
    }
    public function isMiddlewareBasedPlugin() : bool
    {
        return \true;
    }
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->urlHelper = new UrlHelper();
        $this->commonHelper = new CommonHelper();
        $this->ipBlockingHelper = new IpBlockingHelper($this->container);
        $this->options = $this->container->get('options');
        $this->isEnabled = $this->options->get('siteSettings')['uploadScanner']['enabled'] ?? \false;
        $this->logger = $this->container->get('logger');
    }
    /**
     * @param ServerRequestInterface $request  PSR7 request
     * @param ResponseInterface      $response PSR7 response
     * @param callable               $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        // Pluging bypassed/disabled due to phpmussel Core library not compatible with php7.1
        $response = $next($request, $response);
        return $response;
        if (!$request->getAttribute('astraEnabled') || !$this->isEnabled) {
            $response = $next($request, $response);
            return $response;
        }
        if ($request->getAttribute('alreadyAllowed')) {
            $response = $next($request, $response);
            return $response;
        }
        if (0 === \count($_FILES)) {
            $response = $next($request, $response);
            return $response;
        }
        $currentIp = $this->urlHelper->getClientIp();
        if (!$currentIp) {
            $this->logger->warning('Visitor IP detected as null in UploadScanner module, exiting.');
            $response = $next($request, $response);
            return $response;
        }
        //load site settings in object
        $fileMaxSize = $this->options->get('siteSettings')['uploadScanner']['maxUploadSizeMB'];
        $allowedExt = $this->options->get('siteSettings')['uploadScanner']['allowedExtensions'];
        $blockedExt = $this->options->get('siteSettings')['uploadScanner']['blockedExtensions'];
        $allowedExtArray = \explode(',', $allowedExt);
        $blockedExtArray = \explode(',', $blockedExt);
        //create config file with site settings
        $content = "files:\n filesize_limit: \"{$fileMaxSize} MB\"\n filetype_whitelist: \"{$allowedExt}\"\n filetype_blacklist: \"{$blockedExt}\"\n filetype_greylist: \"\"";
        $tempFile = \tempnam(\sys_get_temp_dir(), 'phpMusselConfig_');
        $configPathWithExt = $tempFile . '.' . 'yml';
        try {
            \file_put_contents($configPathWithExt, $content);
        } catch (\Exception $e) {
            $this->logger->error('Could not write config in phpMussel temp file in Upload Scanner Plugin, plugin will not work reliably.');
        }
        //initialize loader and scanner
        $loader = new Loader($configPathWithExt, self::MUSSLE_CACHE_PATH, self::MUSSLE_QUARANTINE_PATH, self::MUSSLE_SIGNATURE_PATH);
        $scanner = new Scanner($loader);
        $results = [];
        // Execute the scan for each file.
        foreach ($_FILES as $key => $file) {
            //true means Problems were detected (scan target is bad/dangerous). False otherwise
            $scanResultBool = $scanner->scan($file['tmp_name'], 2);
            $scanResultBool2 = $scanner->scan($file['tmp_name'], 3);
            $finfo = \finfo_open(\FILEINFO_EXTENSION);
            $ext1 = \finfo_file($finfo, $file['tmp_name']);
            $ext = \explode('/', $ext1)[0];
            //since finfo_file can return outputs like this - jpeg/jpg/jpe/jfif
            foreach ($scanResultBool2 as $key => $value) {
                //cannot use array_key_first because it is not there in php7.1
                $firstKey = $key;
                break;
            }
            $results[$key]['reason'][] = $scanResultBool2[$firstKey];
            if (\count($allowedExtArray) > 0 && \in_array($ext, $allowedExtArray)) {
                //Allowed extensions get checked first
                $fileAllowed = \true;
            } elseif (\count($blockedExtArray) > 0 && \in_array($ext, $blockedExtArray)) {
                $fileAllowed = \false;
                $results[$key]['reason'][] = 'File extension not allowed.';
            } else {
                //if file ext not in both allowed & blocked ext arrays then ext assumed to be allowed
                $fileAllowed = \true;
            }
            if ($scanResultBool || !$fileAllowed) {
                $results[$key]['error'] = \true;
                //this means file does not pass the config and will be flagged as threat.
            } else {
                $results[$key]['error'] = \false;
            }
        }
        // Cleanup.
        unset($scanner, $loader);
        \unlink($configPathWithExt);
        \unlink($tempFile);
        $siteId = $this->container->get('options')->get('siteId');
        //Record in GK
        $ipData = $this->ipBlockingHelper->recordIpBlockingInstance($currentIp, $this->getName());
        $siteMode = $this->container->get('options')->get('siteSettings')['protectionMode'];
        if ('monitoring' != $siteMode) {
            if ($ipData['blockedByGkDueToThresholdReached']) {
                $status = 'blocked';
            } else {
                $status = 'stopped';
            }
        } else {
            $status = 'monitored';
        }
        //$threatGuid = [];
        $attackParameters = [];
        //check if problems found, post threat and show block page.
        $blockPageFlag = \false;
        $printReason = '';
        foreach ($results as $key => $badUploads) {
            if (!$badUploads['error']) {
                //no error
                continue;
            }
            $blockPageFlag = \true;
            $blockRefId = $this->ipBlockingHelper->generateBlockReferenceId($this->getName());
            $printReason .= ' ' . $key . ' - ' . \implode(',', $badUploads['reason']);
            //Record in Symfony
            $threatBody = $this->commonHelper->threatPostPrepare($request, $siteId, $this->container, $status);
            $threatBody['attackedParameter'] = $attackParameters[] = 'FILES.' . $key;
            $threatBody['attackVector'] = \base64_encode($_FILES[$key]['name']);
            $threatBody['expiresAt'] = isset($ipData['blockUntil']) ? $ipData['blockUntil'] : null;
            $threatBody['blockRefId'] = $blockRefId;
            $fnRes = $this->ipBlockingHelper->recordThreatInSymfony($threatBody);
            if ($fnRes) {
                //$decoded = json_decode($fnRes, true);
                //$threatGuid[] = $decoded['@id'];
            } else {
                $this->logger->error('From UploadScannerPlugin - Threat could not be posted to Symfony');
            }
        }
        if ($blockPageFlag) {
            return $this->ipBlockingHelper->blockPageDataPrep($response, $currentIp, \trim($printReason), $this->getName(), $attackParameters, $blockRefId);
            //show block page
        }
        $response = $next($request, $response);
        return $response;
    }
}
/**
 * Description of UploadScannerPlugin.
 *
 * @author aditya
 */
\class_alias('AstraPrefixed\\UploadScannerPlugin', 'UploadScannerPlugin', \false);
