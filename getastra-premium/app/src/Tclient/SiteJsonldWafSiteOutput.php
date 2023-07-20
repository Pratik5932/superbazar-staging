<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AstraPrefixed\GetAstra\Client\Tclient;

use ArrayAccess;
use AstraPrefixed\GetAstra\Api\Client\ObjectSerializer;
/**
 * Description of SiteJsonldWafSiteOutput.
 *
 * @author aditya
 */
class SiteJsonldWafSiteOutput implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;
    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'Site:jsonld-waf_site-output';
    /**
     * Array of property to type mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $openAPITypes = ['context' => 'string', 'id' => 'string', 'type' => 'string', 'id' => 'string', 'url' => 'string', 'domain' => 'string', 'name' => 'string', 'connected' => 'bool', 'disconnected_message' => 'string', 'worker_version' => 'string', 'created_at' => '\\DateTime', 'php_version' => 'string', 'type' => 'string', 'version' => 'string', 'locale' => 'string', 'favorite' => 'bool', 'paused' => 'bool', 'updated_at' => '\\DateTime', 'last_synced_at' => '\\DateTime', 'settings' => 'string[]', 'api_url' => 'string', 'scans' => '\\GetAstra\\Api\\Client\\Model\\ScanJsonldWafSiteOutput[]', 'last_scan' => 'AnyOfScanJsonldWafSiteOutput', 'last_completed_scan' => 'AnyOfScanJsonldWafSiteOutput', 'created_by' => 'string', 'client_api_token' => 'string', 'disable_pretty_urls' => 'bool', 'options' => 'string[]', 'plugins' => 'string[]', 'credentials' => 'string', 'cms' => 'AstraPrefixed\\GetAstra\\Api\\Client\\Model\\SiteCms', 'is_agency' => 'bool', 'team' => 'AnyOfTeamJsonldWafSiteOutput', 'subscription' => 'AnyOfSubscriptionJsonldWafSiteOutput'];
    /**
     * Array of property to format mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $openAPIFormats = ['context' => null, 'id' => null, 'type' => null, 'id' => 'uuid', 'url' => null, 'domain' => null, 'name' => null, 'connected' => null, 'disconnected_message' => null, 'worker_version' => null, 'created_at' => 'date-time', 'php_version' => null, 'type' => null, 'version' => null, 'locale' => null, 'favorite' => null, 'paused' => null, 'updated_at' => 'date-time', 'last_synced_at' => 'date-time', 'settings' => null, 'api_url' => null, 'scans' => null, 'last_scan' => null, 'last_completed_scan' => null, 'created_by' => 'iri-reference', 'client_api_token' => null, 'disable_pretty_urls' => null, 'options' => null, 'plugins' => 'iri-reference', 'credentials' => 'iri-reference', 'cms' => null, 'is_agency' => null, 'team' => null, 'subscription' => null];
    /**
     * Array of property to type mappings. Used for (de)serialization.
     *
     * @return array
     */
    public static function openAPITypes()
    {
        return self::$openAPITypes;
    }
    /**
     * Array of property to format mappings. Used for (de)serialization.
     *
     * @return array
     */
    public static function openAPIFormats()
    {
        return self::$openAPIFormats;
    }
    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name.
     *
     * @var string[]
     */
    protected static $attributeMap = ['context' => '@context', 'id' => '@id', 'type' => '@type', 'id' => 'id', 'url' => 'url', 'domain' => 'domain', 'name' => 'name', 'connected' => 'connected', 'disconnected_message' => 'disconnectedMessage', 'worker_version' => 'workerVersion', 'created_at' => 'createdAt', 'php_version' => 'phpVersion', 'type' => 'type', 'version' => 'version', 'locale' => 'locale', 'favorite' => 'favorite', 'paused' => 'paused', 'updated_at' => 'updatedAt', 'last_synced_at' => 'lastSyncedAt', 'settings' => 'settings', 'api_url' => 'apiUrl', 'scans' => 'scans', 'last_scan' => 'lastScan', 'last_completed_scan' => 'lastCompletedScan', 'created_by' => 'createdBy', 'client_api_token' => 'clientApiToken', 'disable_pretty_urls' => 'disablePrettyUrls', 'options' => 'options', 'plugins' => 'plugins', 'credentials' => 'credentials', 'cms' => 'cms', 'is_agency' => 'isAgency', 'team' => 'team', 'subscription' => 'subscription'];
    /**
     * Array of attributes to setter functions (for deserialization of responses).
     *
     * @var string[]
     */
    protected static $setters = ['context' => 'setContext', 'id' => 'setId', 'type' => 'setType', 'id' => 'setId', 'url' => 'setUrl', 'domain' => 'setDomain', 'name' => 'setName', 'connected' => 'setConnected', 'disconnected_message' => 'setDisconnectedMessage', 'worker_version' => 'setWorkerVersion', 'created_at' => 'setCreatedAt', 'php_version' => 'setPhpVersion', 'type' => 'setType', 'version' => 'setVersion', 'locale' => 'setLocale', 'favorite' => 'setFavorite', 'paused' => 'setPaused', 'updated_at' => 'setUpdatedAt', 'last_synced_at' => 'setLastSyncedAt', 'settings' => 'setSettings', 'api_url' => 'setApiUrl', 'scans' => 'setScans', 'last_scan' => 'setLastScan', 'last_completed_scan' => 'setLastCompletedScan', 'created_by' => 'setCreatedBy', 'client_api_token' => 'setClientApiToken', 'disable_pretty_urls' => 'setDisablePrettyUrls', 'options' => 'setOptions', 'plugins' => 'setPlugins', 'credentials' => 'setCredentials', 'cms' => 'setCms', 'is_agency' => 'setIsAgency', 'team' => 'setTeam', 'subscription' => 'setSubscription'];
    /**
     * Array of attributes to getter functions (for serialization of requests).
     *
     * @var string[]
     */
    protected static $getters = ['context' => 'getContext', 'id' => 'getId', 'type' => 'getType', 'id' => 'getId', 'url' => 'getUrl', 'domain' => 'getDomain', 'name' => 'getName', 'connected' => 'getConnected', 'disconnected_message' => 'getDisconnectedMessage', 'worker_version' => 'getWorkerVersion', 'created_at' => 'getCreatedAt', 'php_version' => 'getPhpVersion', 'type' => 'getType', 'version' => 'getVersion', 'locale' => 'getLocale', 'favorite' => 'getFavorite', 'paused' => 'getPaused', 'updated_at' => 'getUpdatedAt', 'last_synced_at' => 'getLastSyncedAt', 'settings' => 'getSettings', 'api_url' => 'getApiUrl', 'scans' => 'getScans', 'last_scan' => 'getLastScan', 'last_completed_scan' => 'getLastCompletedScan', 'created_by' => 'getCreatedBy', 'client_api_token' => 'getClientApiToken', 'disable_pretty_urls' => 'getDisablePrettyUrls', 'options' => 'getOptions', 'plugins' => 'getPlugins', 'credentials' => 'getCredentials', 'cms' => 'getCms', 'is_agency' => 'getIsAgency', 'team' => 'getTeam', 'subscription' => 'getSubscription'];
    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name.
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }
    /**
     * Array of attributes to setter functions (for deserialization of responses).
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }
    /**
     * Array of attributes to getter functions (for serialization of requests).
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }
    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }
    /**
     * Associative array for storing property values.
     *
     * @var mixed[]
     */
    protected $container = [];
    /**
     * Constructor.
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['context'] = isset($data['context']) ? $data['context'] : null;
        $this->container['id'] = isset($data['id']) ? $data['id'] : null;
        $this->container['type'] = isset($data['type']) ? $data['type'] : null;
        $this->container['id'] = isset($data['id']) ? $data['id'] : null;
        $this->container['url'] = isset($data['url']) ? $data['url'] : null;
        $this->container['domain'] = isset($data['domain']) ? $data['domain'] : null;
        $this->container['name'] = isset($data['name']) ? $data['name'] : null;
        $this->container['connected'] = isset($data['connected']) ? $data['connected'] : null;
        $this->container['disconnected_message'] = isset($data['disconnected_message']) ? $data['disconnected_message'] : null;
        $this->container['worker_version'] = isset($data['worker_version']) ? $data['worker_version'] : null;
        $this->container['created_at'] = isset($data['created_at']) ? $data['created_at'] : null;
        $this->container['php_version'] = isset($data['php_version']) ? $data['php_version'] : null;
        $this->container['type'] = isset($data['type']) ? $data['type'] : null;
        $this->container['version'] = isset($data['version']) ? $data['version'] : null;
        $this->container['locale'] = isset($data['locale']) ? $data['locale'] : null;
        $this->container['favorite'] = isset($data['favorite']) ? $data['favorite'] : null;
        $this->container['paused'] = isset($data['paused']) ? $data['paused'] : null;
        $this->container['updated_at'] = isset($data['updated_at']) ? $data['updated_at'] : null;
        $this->container['last_synced_at'] = isset($data['last_synced_at']) ? $data['last_synced_at'] : null;
        $this->container['settings'] = isset($data['settings']) ? $data['settings'] : null;
        $this->container['api_url'] = isset($data['api_url']) ? $data['api_url'] : null;
        $this->container['scans'] = isset($data['scans']) ? $data['scans'] : null;
        $this->container['last_scan'] = isset($data['last_scan']) ? $data['last_scan'] : null;
        $this->container['last_completed_scan'] = isset($data['last_completed_scan']) ? $data['last_completed_scan'] : null;
        $this->container['created_by'] = isset($data['created_by']) ? $data['created_by'] : null;
        $this->container['client_api_token'] = isset($data['client_api_token']) ? $data['client_api_token'] : null;
        $this->container['disable_pretty_urls'] = isset($data['disable_pretty_urls']) ? $data['disable_pretty_urls'] : null;
        $this->container['options'] = isset($data['options']) ? $data['options'] : null;
        $this->container['plugins'] = isset($data['plugins']) ? $data['plugins'] : null;
        $this->container['credentials'] = isset($data['credentials']) ? $data['credentials'] : null;
        $this->container['cms'] = isset($data['cms']) ? $data['cms'] : null;
        $this->container['is_agency'] = isset($data['is_agency']) ? $data['is_agency'] : null;
        $this->container['team'] = isset($data['team']) ? $data['team'] : null;
        $this->container['subscription'] = isset($data['subscription']) ? $data['subscription'] : null;
    }
    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];
        return $invalidProperties;
    }
    /**
     * Validate all the properties in the model
     * return true if all passed.
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return 0 === \count($this->listInvalidProperties());
    }
    /**
     * Gets context.
     *
     * @return string|null
     */
    public function getContext()
    {
        return $this->container['context'];
    }
    /**
     * Sets context.
     *
     * @param string|null $context context
     *
     * @return $this
     */
    public function setContext($context)
    {
        $this->container['context'] = $context;
        return $this;
    }
    //    /**
    //     * Gets id
    //     *
    //     * @return string|null
    //     */
    //    public function getId() {
    //        return $this->container['id'];
    //    }
    //
    //    /**
    //     * Sets id
    //     *
    //     * @param string|null $id id
    //     *
    //     * @return $this
    //     */
    //    public function setId($id) {
    //        $this->container['id'] = $id;
    //
    //        return $this;
    //    }
    //
    //    /**
    //     * Gets type
    //     *
    //     * @return string|null
    //     */
    //    public function getType() {
    //        return $this->container['type'];
    //    }
    //    /**
    //     * Sets type
    //     *
    //     * @param string|null $type type
    //     *
    //     * @return $this
    //     */
    //    public function setType($type) {
    //        $this->container['type'] = $type;
    //
    //        return $this;
    //    }
    /**
     * Gets id.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->container['id'];
    }
    /**
     * Sets id.
     *
     * @param string|null $id id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->container['id'] = $id;
        return $this;
    }
    /**
     * Gets url.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->container['url'];
    }
    /**
     * Sets url.
     *
     * @param string|null $url url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->container['url'] = $url;
        return $this;
    }
    /**
     * Gets domain.
     *
     * @return string|null
     */
    public function getDomain()
    {
        return $this->container['domain'];
    }
    /**
     * Sets domain.
     *
     * @param string|null $domain domain
     *
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->container['domain'] = $domain;
        return $this;
    }
    /**
     * Gets name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->container['name'];
    }
    /**
     * Sets name.
     *
     * @param string|null $name name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->container['name'] = $name;
        return $this;
    }
    /**
     * Gets connected.
     *
     * @return bool|null
     */
    public function getConnected()
    {
        return $this->container['connected'];
    }
    /**
     * Sets connected.
     *
     * @param bool|null $connected connected
     *
     * @return $this
     */
    public function setConnected($connected)
    {
        $this->container['connected'] = $connected;
        return $this;
    }
    /**
     * Gets disconnected_message.
     *
     * @return string|null
     */
    public function getDisconnectedMessage()
    {
        return $this->container['disconnected_message'];
    }
    /**
     * Sets disconnected_message.
     *
     * @param string|null $disconnected_message disconnected_message
     *
     * @return $this
     */
    public function setDisconnectedMessage($disconnected_message)
    {
        $this->container['disconnected_message'] = $disconnected_message;
        return $this;
    }
    /**
     * Gets worker_version.
     *
     * @return string|null
     */
    public function getWorkerVersion()
    {
        return $this->container['worker_version'];
    }
    /**
     * Sets worker_version.
     *
     * @param string|null $worker_version worker_version
     *
     * @return $this
     */
    public function setWorkerVersion($worker_version)
    {
        $this->container['worker_version'] = $worker_version;
        return $this;
    }
    /**
     * Gets created_at.
     *
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->container['created_at'];
    }
    /**
     * Sets created_at.
     *
     * @param \DateTime|null $created_at created_at
     *
     * @return $this
     */
    public function setCreatedAt($created_at)
    {
        $this->container['created_at'] = $created_at;
        return $this;
    }
    /**
     * Gets php_version.
     *
     * @return string|null
     */
    public function getPhpVersion()
    {
        return $this->container['php_version'];
    }
    /**
     * Sets php_version.
     *
     * @param string|null $php_version php_version
     *
     * @return $this
     */
    public function setPhpVersion($php_version)
    {
        $this->container['php_version'] = $php_version;
        return $this;
    }
    /**
     * Gets type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->container['type'];
    }
    /**
     * Sets type.
     *
     * @param string|null $type type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->container['type'] = $type;
        return $this;
    }
    /**
     * Gets version.
     *
     * @return string|null
     */
    public function getVersion()
    {
        return $this->container['version'];
    }
    /**
     * Sets version.
     *
     * @param string|null $version version
     *
     * @return $this
     */
    public function setVersion($version)
    {
        $this->container['version'] = $version;
        return $this;
    }
    /**
     * Gets locale.
     *
     * @return string|null
     */
    public function getLocale()
    {
        return $this->container['locale'];
    }
    /**
     * Sets locale.
     *
     * @param string|null $locale locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->container['locale'] = $locale;
        return $this;
    }
    /**
     * Gets favorite.
     *
     * @return bool|null
     */
    public function getFavorite()
    {
        return $this->container['favorite'];
    }
    /**
     * Sets favorite.
     *
     * @param bool|null $favorite favorite
     *
     * @return $this
     */
    public function setFavorite($favorite)
    {
        $this->container['favorite'] = $favorite;
        return $this;
    }
    /**
     * Gets paused.
     *
     * @return bool|null
     */
    public function getPaused()
    {
        return $this->container['paused'];
    }
    /**
     * Sets paused.
     *
     * @param bool|null $paused paused
     *
     * @return $this
     */
    public function setPaused($paused)
    {
        $this->container['paused'] = $paused;
        return $this;
    }
    /**
     * Gets updated_at.
     *
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->container['updated_at'];
    }
    /**
     * Sets updated_at.
     *
     * @param \DateTime|null $updated_at updated_at
     *
     * @return $this
     */
    public function setUpdatedAt($updated_at)
    {
        $this->container['updated_at'] = $updated_at;
        return $this;
    }
    /**
     * Gets last_synced_at.
     *
     * @return \DateTime|null
     */
    public function getLastSyncedAt()
    {
        return $this->container['last_synced_at'];
    }
    /**
     * Sets last_synced_at.
     *
     * @param \DateTime|null $last_synced_at last_synced_at
     *
     * @return $this
     */
    public function setLastSyncedAt($last_synced_at)
    {
        $this->container['last_synced_at'] = $last_synced_at;
        return $this;
    }
    /**
     * Gets settings.
     *
     * @return string[]|null
     */
    public function getSettings()
    {
        return $this->container['settings'];
    }
    /**
     * Sets settings.
     *
     * @param string[]|null $settings settings
     *
     * @return $this
     */
    public function setSettings($settings)
    {
        $this->container['settings'] = $settings;
        return $this;
    }
    /**
     * Gets api_url.
     *
     * @return string|null
     */
    public function getApiUrl()
    {
        return $this->container['api_url'];
    }
    /**
     * Sets api_url.
     *
     * @param string|null $api_url api_url
     *
     * @return $this
     */
    public function setApiUrl($api_url)
    {
        $this->container['api_url'] = $api_url;
        return $this;
    }
    /**
     * Gets scans.
     *
     * @return \GetAstra\Api\Client\Model\ScanJsonldWafSiteOutput[]|null
     */
    public function getScans()
    {
        return $this->container['scans'];
    }
    /**
     * Sets scans.
     *
     * @param \GetAstra\Api\Client\Model\ScanJsonldWafSiteOutput[]|null $scans scans
     *
     * @return $this
     */
    public function setScans($scans)
    {
        $this->container['scans'] = $scans;
        return $this;
    }
    /**
     * Gets last_scan.
     *
     * @return AnyOfScanJsonldWafSiteOutput|null
     */
    public function getLastScan()
    {
        return $this->container['last_scan'];
    }
    /**
     * Sets last_scan.
     *
     * @param AnyOfScanJsonldWafSiteOutput|null $last_scan virtual Property of the most recent scan
     *
     * @return $this
     */
    public function setLastScan($last_scan)
    {
        $this->container['last_scan'] = $last_scan;
        return $this;
    }
    /**
     * Gets last_completed_scan.
     *
     * @return AnyOfScanJsonldWafSiteOutput|null
     */
    public function getLastCompletedScan()
    {
        return $this->container['last_completed_scan'];
    }
    /**
     * Sets last_completed_scan.
     *
     * @param AnyOfScanJsonldWafSiteOutput|null $last_completed_scan virtual Property of the last completed scan
     *
     * @return $this
     */
    public function setLastCompletedScan($last_completed_scan)
    {
        $this->container['last_completed_scan'] = $last_completed_scan;
        return $this;
    }
    /**
     * Gets created_by.
     *
     * @return string|null
     */
    public function getCreatedBy()
    {
        return $this->container['created_by'];
    }
    /**
     * Sets created_by.
     *
     * @param string|null $created_by created_by
     *
     * @return $this
     */
    public function setCreatedBy($created_by)
    {
        $this->container['created_by'] = $created_by;
        return $this;
    }
    /**
     * Gets client_api_token.
     *
     * @return string|null
     */
    public function getClientApiToken()
    {
        return $this->container['client_api_token'];
    }
    /**
     * Sets client_api_token.
     *
     * @param string|null $client_api_token client_api_token
     *
     * @return $this
     */
    public function setClientApiToken($client_api_token)
    {
        $this->container['client_api_token'] = $client_api_token;
        return $this;
    }
    /**
     * Gets disable_pretty_urls.
     *
     * @return bool|null
     */
    public function getDisablePrettyUrls()
    {
        return $this->container['disable_pretty_urls'];
    }
    /**
     * Sets disable_pretty_urls.
     *
     * @param bool|null $disable_pretty_urls disable_pretty_urls
     *
     * @return $this
     */
    public function setDisablePrettyUrls($disable_pretty_urls)
    {
        $this->container['disable_pretty_urls'] = $disable_pretty_urls;
        return $this;
    }
    /**
     * Gets options.
     *
     * @return string[]|null
     */
    public function getOptions()
    {
        return $this->container['options'];
    }
    /**
     * Sets options.
     *
     * @param string[]|null $options options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->container['options'] = $options;
        return $this;
    }
    /**
     * Gets plugins.
     *
     * @return string[]|null
     */
    public function getPlugins()
    {
        return $this->container['plugins'];
    }
    /**
     * Sets plugins.
     *
     * @param string[]|null $plugins plugins
     *
     * @return $this
     */
    public function setPlugins($plugins)
    {
        $this->container['plugins'] = $plugins;
        return $this;
    }
    /**
     * Gets credentials.
     *
     * @return string|null
     */
    public function getCredentials()
    {
        return $this->container['credentials'];
    }
    /**
     * Sets credentials.
     *
     * @param string|null $credentials credentials
     *
     * @return $this
     */
    public function setCredentials($credentials)
    {
        $this->container['credentials'] = $credentials;
        return $this;
    }
    /**
     * Gets cms.
     *
     * @return \GetAstra\Api\Client\Model\SiteCms|null
     */
    public function getCms()
    {
        return $this->container['cms'];
    }
    /**
     * Sets cms.
     *
     * @param \GetAstra\Api\Client\Model\SiteCms|null $cms cms
     *
     * @return $this
     */
    public function setCms($cms)
    {
        $this->container['cms'] = $cms;
        return $this;
    }
    /**
     * Gets is_agency.
     *
     * @return bool|null
     */
    public function getIsAgency()
    {
        return $this->container['is_agency'];
    }
    /**
     * Sets is_agency.
     *
     * @param bool|null $is_agency is_agency
     *
     * @return $this
     */
    public function setIsAgency($is_agency)
    {
        $this->container['is_agency'] = $is_agency;
        return $this;
    }
    /**
     * Gets team.
     *
     * @return AnyOfTeamJsonldWafSiteOutput|null
     */
    public function getTeam()
    {
        return $this->container['team'];
    }
    /**
     * Sets team.
     *
     * @param AnyOfTeamJsonldWafSiteOutput|null $team team
     *
     * @return $this
     */
    public function setTeam($team)
    {
        $this->container['team'] = $team;
        return $this;
    }
    /**
     * Gets subscription.
     *
     * @return AnyOfSubscriptionJsonldWafSiteOutput|null
     */
    public function getSubscription()
    {
        return $this->container['subscription'];
    }
    /**
     * Sets subscription.
     *
     * @param AnyOfSubscriptionJsonldWafSiteOutput|null $subscription subscription
     *
     * @return $this
     */
    public function setSubscription($subscription)
    {
        $this->container['subscription'] = $subscription;
        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param int $offset Offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }
    /**
     * Gets offset.
     *
     * @param int $offset Offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
    /**
     * Sets value based on offset.
     *
     * @param int   $offset Offset
     * @param mixed $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (\is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }
    /**
     * Unsets offset.
     *
     * @param int $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }
    /**
     * Gets the string presentation of the object.
     *
     * @return string
     */
    public function __toString()
    {
        return \json_encode(ObjectSerializer::sanitizeForSerialization($this), \JSON_PRETTY_PRINT);
    }
    /**
     * Gets a header-safe presentation of the object.
     *
     * @return string
     */
    public function toHeaderValue()
    {
        return \json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}
