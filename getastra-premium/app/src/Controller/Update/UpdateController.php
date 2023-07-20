<?php

namespace AstraPrefixed\GetAstra\Client\Controller\Update;

use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
use AstraPrefixed\GetAstra\Client\Service\SiteSettingsService;
use AstraPrefixed\GetAstra\Client\Service\UpdateService;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Log\LoggerInterface;
use AstraPrefixed\Psr\SimpleCache\CacheInterface;
use AstraPrefixed\Slim\Http\Request;
use AstraPrefixed\Slim\Http\Response;
use AstraPrefixed\GetAstra\Client\Helper\QueueHelper;
use AstraPrefixed\Slim\Http\StatusCode;
use AstraPrefixed\Respect\Validation\Validator as v;
class UpdateController
{
    /**
     * @var UpdateService
     */
    private $updateService;
    private $container;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var QueueHelper
     */
    private $queueHelper;
    /**
     * @var CacheInterface
     */
    private $options;
    /**
     * @var SiteSettingsService
     */
    private $siteSettingsService;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->options = $container->get('options');
        $this->updateService = $container->get('update');
        $this->siteSettingsService = $container->get('siteSettings');
        $this->logger = $this->container->get('logger');
        $this->queueHelper = new QueueHelper($container);
    }
    /**
     * Endpoint that handles gatekeeper source code update
     */
    public function triggerUpdate(Request $request, Response $response)
    {
        $validate = v::key('gkUpdateJobId', v::regex("/^[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->validate(\json_decode($request->getBody()->getContents(), \true));
        $gkUpdateJobId = $request->getParam('gkUpdateJobId');
        if (!$gkUpdateJobId || !$validate) {
            return $response->withStatus(StatusCode::HTTP_BAD_REQUEST)->withJson(['error' => 'Please send gkUpdateJobId to start GK update.']);
        }
        $this->options->set($this->updateService::GK_UPDATE_JOB_ID_OPTIONS_KEY, $gkUpdateJobId);
        //add tasks and return response to symfony
        $task = ['handler' => $this->queueHelper::GK_UPDATE_TASK, 'metadata' => $gkUpdateJobId];
        $this->queueHelper->addTaskToQueue($task);
        //$this->logger->error('task added - '.json_encode($task));
        return $response->withStatus(StatusCode::HTTP_OK)->withJson(['error' => 'Gatekeeper update job received.']);
    }
    /**
     * Function to update ipRules,Site settings and exceptions.
     *
     * @return Response $newResponse
     */
    public function updateSiteSettings(Request $request, Response $response)
    {
        /* @var $options CacheInterface */
        $options = $this->container->get('options');
        $requestBody = $request->getParsedBody();
        if (!\is_array($requestBody) || \count($requestBody) <= 0) {
            $newResponse = $response->withStatus(400);
            $body = $newResponse->getBody();
            $body->write(\json_encode($requestBody));
            return $newResponse;
        }
        foreach ($requestBody as $key => $value) {
            //cannot use array_key_first because it is not there in php7.1
            $firstKey = $key;
            break;
        }
        $deleteKey = \array_key_exists('delete', $requestBody);
        switch ($firstKey) {
            case $this->siteSettingsService::SITE_OPTIONS_KEY:
                $fullBody = $request->getParsedBody();
                $validation = $this->validateInput($fullBody, $this->siteSettingsService::SITE_OPTIONS_KEY);
                if (!$validation) {
                    $newResponse = $response->withStatus(400)->withJson(['errorMessage' => 'Missing or invalid site settings/options data.']);
                    return $newResponse;
                }
                $options->set($this->siteSettingsService::SITE_OPTIONS_KEY, $fullBody[$firstKey]);
                break;
            case $this->siteSettingsService::IP_RULES_KEY:
                //incrementally update IP rules
                $ipRuleSentInRequest = $request->getParsedBodyParam($firstKey) ?? \false;
                $validation = $this->validateInput($requestBody, $this->siteSettingsService::IP_RULES_KEY);
                $ipSentInRequest = $request->getParsedBodyParam($firstKey)['ipAddress'] ?? \false;
                if (!$ipRuleSentInRequest || !$ipSentInRequest || !$validation) {
                    $this->logger->error('IP-rule sync fail, no IP rule not sent by core API');
                    $newResponse = $response->withStatus(400)->withJson(['errorMessage' => 'Missing or invalid ip-rule data.']);
                    return $newResponse;
                }
                if ($deleteKey) {
                    //delete rule
                    $this->removeIpRule($options, $ipRuleSentInRequest);
                } else {
                    $this->incrementalIpRuleSync($options, $ipRuleSentInRequest);
                }
                break;
            case $this->siteSettingsService::EXCEPTIONS_KEY:
                //incrementally update Exceptions
                $exceptionSentInRequest = $request->getParsedBodyParam($firstKey) ?? \false;
                $validation = $this->validateInput($requestBody, $this->siteSettingsService::EXCEPTIONS_KEY);
                if (!$exceptionSentInRequest || !$validation) {
                    $this->logger->error('Exception sync fail, no exception sent by core API');
                    $newResponse = $response->withStatus(400)->withJson(['errorMessage' => 'Missing or invalid exception data.']);
                    return $newResponse;
                }
                if ($deleteKey) {
                    //delete rule
                    $this->removeExceptionRule($options, $exceptionSentInRequest);
                } else {
                    //update rule
                    $this->incrementalExceptionRuleSync($options, $exceptionSentInRequest);
                }
                break;
            case $this->siteSettingsService::BOOSTER_RULES_KEY:
                //incrementally update booster
                $boosterSentInRequest = $request->getParsedBodyParam($firstKey) ?? \false;
                $validation = $this->validateInput($requestBody, $this->siteSettingsService::BOOSTER_RULES_KEY);
                if (!$boosterSentInRequest || !$validation) {
                    $this->logger->error('Booster sync fail, no rule sent by core API');
                    $newResponse = $response->withStatus(400)->withJson(['errorMessage' => 'Missing or invalid booster data.']);
                    return $newResponse;
                }
                $boosterSentInRequest['status'] = \filter_var($boosterSentInRequest['status'], \FILTER_VALIDATE_BOOLEAN);
                if ($deleteKey) {
                    //delete rule
                    $this->removeBoosterRule($options, $boosterSentInRequest, $request);
                } else {
                    //update rule
                    $this->incrementalBoosterRuleSync($options, $boosterSentInRequest);
                }
                break;
            case $this->siteSettingsService::FULL_SITE_OBJECT_KEY:
                $validation = $this->validateInput($requestBody, $this->siteSettingsService::FULL_SITE_OBJECT_KEY);
                if (!$validation) {
                    $newResponse = $response->withStatus(400)->withJson(['errorMessage' => 'Missing or invalid site site entity data.']);
                    return $newResponse;
                }
                $options->set($this->siteSettingsService::FULL_SITE_OBJECT_KEY, $request->getParsedBody());
                break;
        }
        $newResponse = $response->withStatus(204)->withHeader('GK-Handled', 'true');
        return $newResponse;
    }
    /**
     * @deprecated
     */
    public function bulkOptionsSync(Request $request, Response $response)
    {
        $this->siteSettingsService->saveSiteSettingsLocally();
        $newResponse = $response->withStatus(204);
        return $newResponse;
    }
    /**
     * This function implements a indirect technique to sync the options in GK.
     * When request is received on this endpoint from symfony, this function sets the CACHE_EXPIRY_KEY key in siteSettings
     * to -1 day.
     * Now whenever GK receives the next request (it can be any request), it will see that cache has expired so it
     * will update its options automatically before the request is served.
     *
     * this was done because this code never worked - https://stackoverflow.com/questions/15273570/continue-processing-php-after-sending-http-response
     */
    public function syncAll(Request $request, Response $response)
    {
        $options = $this->container->get('options');
        $newCacheExpiry = (new \DateTime('-1 day', new \DateTimeZone('UTC')))->format('c');
        $options->set($this->siteSettingsService::CACHE_EXPIRY_KEY, $newCacheExpiry);
        $newResponse = $response->withStatus(204);
        return $newResponse;
    }
    /**
     * Helper function to incrementally update/sync a single booster rule in GK options.
     * This function modifies the options sent to it.
     * Handles both new booster rules being added and existing booster rule being modified.
     *
     * @return bool true if new booster rule was added/updated, false if booster rule was already present in existing rule set
     */
    private function incrementalBoosterRuleSync($options, $boosterSentInRequest)
    {
        $allExistingBoosterRules = $options->get($this->siteSettingsService::BOOSTER_RULES_KEY);
        $notPresentFlag = \true;
        $existingKey = null;
        foreach ($allExistingBoosterRules as $key => $boosterRule) {
            if ($boosterRule['id'] == $boosterSentInRequest['id']) {
                $notPresentFlag = \false;
                $existingKey = $key;
            }
        }
        if ($notPresentFlag) {
            //new rule being added
            $allExistingBoosterRules[] = $boosterSentInRequest;
            $options->set($this->siteSettingsService::BOOSTER_RULES_KEY, $allExistingBoosterRules);
            return \true;
        } else {
            //existing rule being modified
            $allExistingBoosterRules[$existingKey] = $boosterSentInRequest;
            $options->set($this->siteSettingsService::BOOSTER_RULES_KEY, $allExistingBoosterRules);
            return \true;
        }
    }
    /**
     * Helper function to incrementally remove a single booster rule in GK options.
     * This function modifies the options sent to it.
     *
     * @return bool true if booster rule was removed, false if booster rule was not present and hence not removed
     */
    private function removeBoosterRule($options, $boosterSentInRequest)
    {
        $allExistingBoosterRules = $options->get($this->siteSettingsService::BOOSTER_RULES_KEY);
        $existingKey = null;
        foreach ($allExistingBoosterRules as $key => $boosterRule) {
            //strict comparison wont work, diff types,
            //also cant compare IDs since on delete operation symfony returns id as null
            if ($boosterRule['id'] == $boosterSentInRequest['readOnlyId']) {
                $existingKey = $key;
            }
        }
        if (isset($existingKey)) {
            //delete the rule
            unset($allExistingBoosterRules[$existingKey]);
            $options->set($this->siteSettingsService::BOOSTER_RULES_KEY, $allExistingBoosterRules);
            return \true;
        } else {
            //rule not found do nothing
            return \false;
        }
    }
    /**
     * Helper function to incrementally update/sync a single IP rule in GK options.
     * This function modifies the options sent to it.
     * Handles both new IP rules being added and existing IP rule being modified.
     *
     * @return bool true if new IP rule was added/updated, false if IP rule was already present in existing rule set
     */
    private function incrementalIpRuleSync($options, $ipRuleSentInRequest)
    {
        $allExistingIpRules = $options->get($this->siteSettingsService::IP_RULES_KEY);
        $notPresentFlag = \true;
        $existingKey = null;
        foreach ($allExistingIpRules as $key => $ipRules) {
            if ($ipRules['id'] == $ipRuleSentInRequest['id']) {
                $notPresentFlag = \false;
                $existingKey = $key;
            }
        }
        if ($notPresentFlag) {
            //new rule being added
            $allExistingIpRules[] = $ipRuleSentInRequest;
            $options->set($this->siteSettingsService::IP_RULES_KEY, $allExistingIpRules);
            return \true;
        } else {
            //existing rule being modified
            $allExistingIpRules[$existingKey] = $ipRuleSentInRequest;
            $options->set($this->siteSettingsService::IP_RULES_KEY, $allExistingIpRules);
            return \true;
        }
    }
    /**
     * Helper function to incrementally remove a single IP rule in GK options.
     * This function modifies the options sent to it.
     *
     * @return bool true if IP rule was removed, false if IP rule was not present and hence not removed
     */
    private function removeIpRule($options, $ipRuleSentInRequest)
    {
        $allExistingIpRules = $options->get($this->siteSettingsService::IP_RULES_KEY);
        $existingKey = -1;
        foreach ($allExistingIpRules as $key => $ipRules) {
            //strict comparison wont work, diff datatypes,
            //also cant compare IDs since on delete operation symfony returns id as null
            //so comparing readOnlyId
            if ($ipRules['id'] == $ipRuleSentInRequest['readOnlyId']) {
                $existingKey = $key;
            }
        }
        if ($existingKey > -1) {
            //0th key will be falsy and equal to null so dont use strict comparison
            //delete the rule
            unset($allExistingIpRules[$existingKey]);
            $options->set($this->siteSettingsService::IP_RULES_KEY, $allExistingIpRules);
            return \true;
        } else {
            //rule not found do nothing
            return \false;
        }
    }
    /**
     * Helper function to incrementally update/sync a single Exception rule in GK options.
     * This function modifies the options sent to it.
     *
     * @return bool true if new Exception rule was added, false if exception rule was already present in existing rule set or exception couldnt be added
     */
    private function incrementalExceptionRuleSync($options, $exceptionSentInRequest)
    {
        if (\false === @\preg_match('/^' . $exceptionSentInRequest['parameter'] . '$/', '')) {
            //strict === type check necessary, only if preg match is false then reject. preg_match result===0 is OK
            return \false;
        }
        $allExistingExceptions = $options->get($this->siteSettingsService::EXCEPTIONS_KEY);
        $notPresentFlag = \true;
        $existingExceptionKey = null;
        foreach ($allExistingExceptions as $key => $exception) {
            if ($exception['id'] == $exceptionSentInRequest['id']) {
                $notPresentFlag = \false;
                $existingExceptionKey = $key;
            }
        }
        if ($notPresentFlag) {
            //new rule being added
            $allExistingExceptions[] = $exceptionSentInRequest;
            $options->set($this->siteSettingsService::EXCEPTIONS_KEY, $allExistingExceptions);
            return \true;
        } else {
            //modifying existing exception rule.
            $allExistingExceptions[$existingExceptionKey] = $exceptionSentInRequest;
            $options->set($this->siteSettingsService::EXCEPTIONS_KEY, $allExistingExceptions);
            return \true;
        }
    }
    /**
     * Helper function to incrementally remove a single Exception rule in GK options.
     * This function modifies the options sent to it.
     *
     * @return bool true if Exception removed, false if exception rule not present and hence not removed
     */
    private function removeExceptionRule($options, $exceptionSentInRequest)
    {
        $allExistingExceptions = $options->get($this->siteSettingsService::EXCEPTIONS_KEY);
        $existingExceptionKey = -1;
        foreach ($allExistingExceptions as $key => $exception) {
            //strict comparison wont work, diff types
            //also cant compare IDs since on delete operation symfony returns id as null
            if ($exception['parameter'] == $exceptionSentInRequest['parameter'] && $exception['method'] == $exceptionSentInRequest['method']) {
                $existingExceptionKey = $key;
            }
        }
        if ($existingExceptionKey > -1) {
            //0th key will be falsy and equal to null so dont use strict comparison
            //rule found so delete
            unset($allExistingExceptions[$existingExceptionKey]);
            $options->set($this->siteSettingsService::EXCEPTIONS_KEY, $allExistingExceptions);
            return \true;
        } else {
            //rule not found
            return \false;
        }
    }
    /**
     * validates ip-rules,exception,booster input sent from symfony.
     * ASTRA DEBUG MODE 
     */
    private function validateInput($input, $type)
    {
        $deleteKey = \array_key_exists('delete', $input);
        switch ($type) {
            case $this->siteSettingsService::IP_RULES_KEY:
                $validate = v::key('ipRules', v::key('@type', v::stringType()->equals('IpRule')->notEmpty())->key('site', v::key('@id', v::regex("/^(?:\\/api\\/waf\\/sites\\/)[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('@type', v::stringType()->equals('Site'))->key('workerVersion', v::stringType())->key('phpVersion', v::stringType())->key('version', v::stringType())->key('locale', v::stringType()))->key('ipAddress', v::ip('*', \FILTER_FLAG_IPV6)->notEmpty())->key('createdBy', v::key('@id', v::regex("/^(?:\\/api\\/users\\/)[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('@type', v::stringType()->equals('User'))->key('firstName', v::stringType()->alnum()->notEmpty())->key('lastName', v::stringType()->alnum()))->key('createdAt', v::regex("/^(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(\\+|-)(\\d{2}):(\\d{2})\$/"))->key('updatedAt', v::regex("/^(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(\\+|-)(\\d{2}):(\\d{2})\$/"))->key('note', v::regex("/^[a-zA-Z0-9-,_.\\/:()@#&\\[\\]\\{\\} ]+\$/"))->key('type', v::in(['trust', 'block'])->notEmpty()))->validate($input);
                if ($deleteKey) {
                    $validate2 = v::key('delete', v::boolVal())->key('ipRules', v::key('readOnlyId', v::regex("/^[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/")))->validate($input);
                } else {
                    $validate2 = v::key('ipRules', v::key('@id', v::regex("/^(?:\\/api\\/waf\\/ip-rules\\/)[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('id', v::regex("/^[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/")))->validate($input);
                }
                return $validate && $validate2 ? \true : \false;
                break;
            case $this->siteSettingsService::EXCEPTIONS_KEY:
                $validate = v::key('exceptions', v::key('@type', v::stringType()->equals('Exception'))->key('site', v::key('@id', v::regex("/^(?:\\/api\\/waf\\/sites\\/)[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('@type', v::stringType()->equals('Site'))->key('workerVersion', v::stringType())->key('phpVersion', v::stringType())->key('version', v::stringType())->key('locale', v::stringType()))->key('parameter', v::stringType()->notEmpty())->key('method', v::in(['bypass', 'html', 'json', 'url'])->notEmpty())->key('createdBy', v::key('@id', v::regex("/^(?:\\/api\\/users\\/)[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('@type', v::stringType()->equals('User'))->key('firstName', v::stringType()->alnum()->notEmpty())->key('lastName', v::stringType()->alnum()))->key('createdAt', v::regex("/^(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(\\+|-)(\\d{2}):(\\d{2})\$/"))->key('note', v::regex("/^[a-zA-Z0-9-,_.\\/:()@#&\\[\\]\\{\\} ]+\$/")))->validate($input);
                if ($deleteKey) {
                    $validate2 = v::key('delete', v::boolVal())->validate($input);
                } else {
                    $validate2 = v::key('exceptions', v::key('id', v::regex("/^[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('@id', v::regex("/^(?:\\/api\\/waf\\/exceptions\\/)[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/")))->validate($input);
                }
                $validate3 = \true;
                $paramValue = \explode('.', $input['exceptions']['parameter']);
                if (\in_array($paramValue[0], ['GET', 'POST', 'PUT', 'PATCH', 'COOKIE', 'FILE', 'REQUEST', 'DELETE', 'SERVER', 'HEADER'])) {
                    if (\false === (bool) @\preg_match("/^[A-Za-z0-9\\[\\]\\-_.]+\$/", $paramValue[1])) {
                        $validate3 = \false;
                    }
                } else {
                    $validate3 = \false;
                }
                return $validate && $validate2 && $validate3 ? \true : \false;
                break;
            case $this->siteSettingsService::BOOSTER_RULES_KEY:
                $validate = v::key('boosters', v::key('@type', v::stringType()->equals('Booster'))->key('site', v::key('@id', v::regex("/^(?:\\/api\\/waf\\/sites\\/)[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('@type', v::stringType()->equals('Site'))->key('workerVersion', v::stringType())->key('phpVersion', v::stringType())->key('version', v::stringType())->key('locale', v::stringType()))->key('name', v::regex("/^[a-zA-Z0-9-,_.\\/:()@#&\\[\\]\\{\\} ]+\$/")->stringType())->key('status', v::boolVal())->key('jsonTreeFormat', v::notEmpty())->key('jsonLogicFormat', v::notEmpty())->key('createdBy', v::key('@id', v::regex("/^(?:\\/api\\/users\\/)[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('@type', v::stringType()->equals('User'))->key('firstName', v::stringType()->alnum()->notEmpty())->key('lastName', v::stringType()->alnum()))->key('createdAt', v::regex("/^(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(\\+|-)(\\d{2}):(\\d{2})\$/"))->key('updatedAt', v::regex("/^(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(\\+|-)(\\d{2}):(\\d{2})\$/"))->key('priority', v::numeric())->key('action', v::in(['trust', 'block'])->notEmpty()))->validate($input);
                if ($deleteKey) {
                    $validate2 = v::key('delete', v::boolVal())->validate($input);
                } else {
                    $validate2 = v::key('boosters', v::key('id', v::regex("/^[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('@id', v::regex("/^(?:\\/api\\/waf\\/boosters\\/)[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/")))->validate($input);
                }
                return $validate && $validate2 ? \true : \false;
                break;
            case $this->siteSettingsService::SITE_OPTIONS_KEY:
                $validate = v::key('siteSettings', v::key('waf', v::key('httpMethods', v::arrayType())->key('blockedCountry', v::arrayType())->key('trustedCountry', v::arrayType())->key('customThresholds', v::key('attackThreshold', v::oneOf(v::numeric(), v::nullType()))->key('loginBlockDuration', v::oneOf(v::numeric(), v::nullType()))->key('attackBlockDuration', v::oneOf(v::numeric(), v::nullType()))->key('loginFailureThreshold', v::oneOf(v::numeric(), v::nullType())))->key('allowForeignChars', v::boolVal())->key('defaultThresholds', v::key('attackThresholdLow', v::numeric()->notEmpty())->key('attackThresholdHigh', v::numeric()->notEmpty())->key('attackThresholdMedium', v::numeric()->notEmpty())->key('loginBlockDurationLow', v::numeric()->notEmpty())->key('attackBlockDurationLow', v::numeric()->notEmpty())->key('loginBlockDurationHigh', v::numeric()->notEmpty())->key('attackBlockDurationHigh', v::numeric()->notEmpty())->key('loginBlockDurationMedium', v::numeric()->notEmpty())->key('loginFailureThresholdLow', v::numeric()->notEmpty())->key('attackBlockDurationMedium', v::numeric()->notEmpty())->key('loginFailureThresholdHigh', v::numeric()->notEmpty())->key('loginFailureThresholdMedium', v::numeric()->notEmpty()))->key('verifySearchCrawlers', v::boolVal()))->key('email', v::key('alerts', v::key('dailyReports', v::boolVal())->key('ipRuleChanges', v::boolVal())->key('websiteLogins', v::boolVal())->key('monthlyReports', v::boolVal()))->key('dailyLimit', v::numeric()), \false)->key('slack', v::key('alerts', v::key('dailyReports', v::boolVal())->key('ipRuleChanges', v::boolVal())->key('websiteLogins', v::boolVal())->key('monthlyReports', v::boolVal()))->key('channel', v::stringType())->key('dailyLimit', v::numeric())->key('webhookUrl', v::stringType())->key('hourlyLimit', v::numeric()), \false)->key('scanner', v::key('ignoredFiles', v::arrayType())->key('ignoredPaths', v::arrayType())->key('scanInterval', v::numeric())->key('scanSchedule', v::stringType())->key('lowResourceMode', v::boolVal())->key('requestedScanEmailNotifications', v::boolVal())->key('scheduledScanEmailNotifications', v::boolVal()))->key('uploadScanner', v::key('enabled', v::boolVal())->key('maxUploadSizeMB', v::numeric())->key('allowedExtensions', v::stringType())->key('blockedExtensions', v::stringType()))->key('securityLevel', v::stringType()->notEmpty())->key('protectionMode', v::stringType()->notEmpty())->key('protectionEnabled', v::boolVal())->key('loginProtection', v::boolVal())->key('autoUpdateGatekeeper', v::boolVal())->key('placeholderUrl', v::boolVal(), \false)->key('sealConfigured', v::boolVal(), \false))->validate($input);
                return $validate;
                break;
            case $this->siteSettingsService::FULL_SITE_OBJECT_KEY:
                $validation1 = v::key('@context', v::stringType()->equals('/api/contexts/Site'))->key('@id', v::regex("/^(?:\\/api\\/waf\\/sites\\/)[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('@type', v::stringType()->equals('Site'))->key('id', v::regex("/^[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('@url', v::stringType(), \false)->key('domain', v::stringType(), \false)->key('name', v::stringType())->key('workerVersion', v::stringType(), \false)->key('disconnectedMessage', v::stringType(), \false)->key('createdAt', v::regex("/^(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(\\+|-)(\\d{2}):(\\d{2})\$/"))->key('phpVersion', v::stringType(), \false)->key('type', v::stringType(), \false)->key('version', v::stringType(), \false)->key('locale', v::stringType(), \false)->key('favorite', v::boolVal())->key('paused', v::boolVal())->key('updatedAt', v::regex("/^(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(\\+|-)(\\d{2}):(\\d{2})\$/"))->key('lastSyncedAt', v::regex("/^(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(\\+|-)(\\d{2}):(\\d{2})\$/"), \false)->key('settings', v::arrayType(), \false)->key('apiUrl', v::stringType(), \false)->key('lastScan', v::key('@id', v::regex("/^(?:\\/api\\/waf\\/plugins\\/scanner\\/scans\\/)[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('@type', v::stringType()->equals('Scan'))->key('status', v::stringType())->key('statusDesc', v::stringType())->key('result', v::stringType())->key('createdAt', v::regex("/^(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(\\+|-)(\\d{2}):(\\d{2})\$/"))->key('issuesCount', v::numeric())->key('duration', v::numeric()), \false)->key('lastCompletedScan', v::key('@id', v::regex("/^(?:\\/api\\/waf\\/plugins\\/scanner\\/scans\\/)[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('@type', v::stringType()->equals('Scan'))->key('status', v::stringType())->key('statusDesc', v::stringType())->key('result', v::stringType())->key('createdAt', v::regex("/^(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(\\+|-)(\\d{2}):(\\d{2})\$/"))->key('issuesCount', v::numeric())->key('duration', v::numeric()), \false)->key('createdBy', v::regex("/^(?:\\/api\\/users\\/)[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('options', v::arrayType(), \false)->key('credentials', v::regex("/^(?:\\/api\\/credentials\\/)[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('cms', v::stringType(), \false)->key('isAgency', v::boolVal())->key('team', v::key('@id', v::regex("/^(?:\\/api\\/teams\\/)[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('@type', v::stringType()->equals('Team'))->key('id', v::regex("/^[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('organization', v::regex("/^(?:\\/api\\/organizations\\/)[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('name', v::stringType()))->key('subscription', v::key('@id', v::regex("/^(?:\\/api\\/subscriptions\\/)[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('@type', v::stringType()->equals('Subscription'))->key('id', v::regex("/^[{]?[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}[}]?\$/"))->key('providerSubscriptionId', v::numeric(), \false)->key('plan', v::key('@id', v::stringType())->key('@type', v::stringType()->equals('Plan'))->key('billingFrequency', v::stringType())->key('active', v::boolVal())->key('slug', v::stringType())->key('product', v::stringType())->key('name', v::stringType())->key('siteLimit', v::numeric())->key('stagingSiteLimit', v::numeric())->key('malwareRemovalAfter', v::numeric(), \false)->key('teamMemberLimit', v::numeric())->key('whitelabelAllowed', v::boolVal()))->key('createdAt', v::regex("/^(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(\\+|-)(\\d{2}):(\\d{2})\$/"))->key('updatedAt', v::regex("/^(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(\\+|-)(\\d{2}):(\\d{2})\$/"))->key('provider', v::stringType())->key('nextBillDate', v::stringType(), \false)->key('nextPaymentAmount', v::numeric(), \false)->key('status', v::stringType())->key('retryAttemptNumber', v::numeric())->key('activeSitesCount', v::numeric()), \false)->key('state', v::stringType())->key('gkSync', v::boolVal(), \false)->key('gkSyncErrorReason', v::stringType(), \false)->key('autoUpdateGk', v::stringType())->key('isMalwareCleanupAvailable', v::boolVal())->validate($input);
                if (isset($input['options'])) {
                    $settings = ['siteSettings' => $input['options']];
                    $validation2 = $this->validateInput($settings, $this->siteSettingsService::SITE_OPTIONS_KEY);
                } else {
                    $validation2 = \true;
                }
                return $validation1 && $validation2 ? \true : \false;
                break;
            default:
                return \false;
                break;
        }
    }
}
