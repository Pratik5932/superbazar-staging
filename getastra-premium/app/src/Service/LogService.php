<?php

namespace AstraPrefixed\GetAstra\Client\Service;

use InvalidArgumentException;
use AstraPrefixed\League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use AstraPrefixed\League\OAuth2\Client\Provider\GenericProvider;
use AstraPrefixed\League\OAuth2\Client\Token\AccessToken;
use LogicException;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Log\LoggerInterface;
use AstraPrefixed\Psr\SimpleCache\CacheInterface;
use AstraPrefixed\GetAstra\Client\Tclient\Configuration;
use AstraPrefixed\GetAstra\Client\Tclient\SiteApi;
use AstraPrefixed\GuzzleHttp\Client;
use AstraPrefixed\GetAstra\Client\Service\SiteSettingsService;
/**
 * LogService is different from logger(monolog)
 */
class LogService
{
    /**
     * @var CacheInterface
     */
    private $options;
    private $container;
    private $availableLog;
    public const GK_DISCONNECT_LOG_KEY = 'gkDisconnectLog';
    public const GK_UPDATER_LOG_KEY = 'gkUpdaterLog';
    // @todo future
    public const GK_LOGIN_LOG_KEY = 'gkLoginLog';
    private const LOG_EXPIRY = 259200;
    // 3 days expiry
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->options = $container->get('options');
        $this->availableLog = [self::GK_DISCONNECT_LOG_KEY, self::GK_LOGIN_LOG_KEY];
    }
    /**
     * gets all the log entries for a particular stream
     * 
     * @future if log gets too big then a pagination for this
     */
    public function getStackTrace($logName)
    {
        if (!$this->isLogAvailable($logName)) {
            return null;
        }
        return $this->options->get(self::GK_DISCONNECT_LOG_KEY, null);
    }
    /**
     * Adds a log entry for a particular stream
     * 
     * @param string $message Log message
     * @param string $slug    Log slug
     * @param string $logName Log Stream
     * @param string $timestamp timestamp in any format
     * 
     * @future maybe enforce timestmap formats
     */
    public function setLog($message, $slug, $logName, $timestamp = null)
    {
        if (!$this->isLogAvailable($logName)) {
            return;
        }
        $existing = $this->getStackTrace($logName);
        if (\is_null($existing)) {
            $existing = [];
        }
        if (\is_null($timestamp)) {
            $timestamp = (new \DateTime())->format('c');
        }
        \array_push($existing, ['reason' => $message, 'createdAt' => $timestamp, 'slug' => $slug]);
        $this->options->set($logName, $existing, self::LOG_EXPIRY);
        return;
    }
    /**
     * Gets the last/latest log entry for a particular stream
     * 
     * @param string $logname Log stream name for which logs are required
     * @param string $key if you want any specific key in the log [reason,createdAt,slug]
     * 
     * @future maybe different log streams can have different types of keys in each log
     */
    public function getLatestLog(string $logName, string $key = null)
    {
        if (!$this->isLogAvailable($logName)) {
            return null;
        }
        $existing = $this->getStackTrace($logName);
        if (\is_null($existing) || \count($existing) <= 0) {
            return null;
        } else {
            if (\in_array($key, ['reason', 'createdAt', 'slug'])) {
                return $existing[\count($existing) - 1][$key];
            } else {
                return $existing[\count($existing) - 1];
            }
        }
    }
    /**
     * Helper function to check if stream/log name is available or not
     * 
     * @param string $logName Log Stream
     */
    private function isLogAvailable($logName)
    {
        if (\in_array($logName, $this->availableLog)) {
            return \true;
        }
        return \false;
    }
    /**
     * function deletes all logs for a particular stream name
     * 
     * @param string $logName Log Stream
     */
    public function clearAllLogs($logName)
    {
        if (!$this->isLogAvailable($logName)) {
            return null;
        }
        $this->options->set($logName, null);
        return;
    }
}
