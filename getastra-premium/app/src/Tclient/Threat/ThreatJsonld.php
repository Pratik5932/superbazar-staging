<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AstraPrefixed\GetAstra\Client\Tclient;

use ArrayAccess;
/**
 * Description of ThreatJsonld.
 *
 * @author aditya
 */
class ThreatJsonld implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;
    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'Threat:jsonld';
    /**
     * Array of property to type mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $openAPITypes = ['context' => 'string', 'id' => 'string', 'type' => 'string', 'id' => 'int', 'site' => 'string', 'ip_address' => 'string', 'useragent' => 'string', 'country' => 'string', 'device' => 'string', 'os' => 'string', 'attacked_url' => 'string', 'attacked_parameter' => 'string', 'attack_vector' => 'string', 'blocking_status' => 'AstraPrefixed\\GetAstra\\Api\\Client\\Model\\BlockingStatus', 'raw_http_request' => 'string', 'created_at' => '\\DateTime', 'false_positive' => 'bool', 'waf_rule' => 'string[]'];
    /**
     * Array of property to format mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $openAPIFormats = ['context' => null, 'id' => null, 'type' => null, 'id' => null, 'site' => 'iri-reference', 'ip_address' => null, 'useragent' => null, 'country' => null, 'device' => null, 'os' => null, 'attacked_url' => null, 'attacked_parameter' => null, 'attack_vector' => null, 'blocking_status' => null, 'raw_http_request' => null, 'created_at' => 'date-time', 'false_positive' => null, 'waf_rule' => 'iri-reference'];
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
    protected static $attributeMap = ['context' => '@context', 'id' => '@id', 'type' => '@type', 'id' => 'id', 'site' => 'site', 'ip_address' => 'ipAddress', 'useragent' => 'useragent', 'country' => 'country', 'device' => 'device', 'os' => 'os', 'attacked_url' => 'attackedUrl', 'attacked_parameter' => 'attackedParameter', 'attack_vector' => 'attackVector', 'blocking_status' => 'blockingStatus', 'raw_http_request' => 'rawHttpRequest', 'created_at' => 'createdAt', 'false_positive' => 'falsePositive', 'waf_rule' => 'wafRule'];
    /**
     * Array of attributes to setter functions (for deserialization of responses).
     *
     * @var string[]
     */
    protected static $setters = ['context' => 'setContext', 'id' => 'setId', 'type' => 'setType', 'id' => 'setId', 'site' => 'setSite', 'ip_address' => 'setIpAddress', 'useragent' => 'setUseragent', 'country' => 'setCountry', 'device' => 'setDevice', 'os' => 'setOs', 'attacked_url' => 'setAttackedUrl', 'attacked_parameter' => 'setAttackedParameter', 'attack_vector' => 'setAttackVector', 'blocking_status' => 'setBlockingStatus', 'raw_http_request' => 'setRawHttpRequest', 'created_at' => 'setCreatedAt', 'false_positive' => 'setFalsePositive', 'waf_rule' => 'setWafRule'];
    /**
     * Array of attributes to getter functions (for serialization of requests).
     *
     * @var string[]
     */
    protected static $getters = ['context' => 'getContext', 'id' => 'getId', 'type' => 'getType', 'id' => 'getId', 'site' => 'getSite', 'ip_address' => 'getIpAddress', 'useragent' => 'getUseragent', 'country' => 'getCountry', 'device' => 'getDevice', 'os' => 'getOs', 'attacked_url' => 'getAttackedUrl', 'attacked_parameter' => 'getAttackedParameter', 'attack_vector' => 'getAttackVector', 'blocking_status' => 'getBlockingStatus', 'raw_http_request' => 'getRawHttpRequest', 'created_at' => 'getCreatedAt', 'false_positive' => 'getFalsePositive', 'waf_rule' => 'getWafRule'];
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
        $this->container['site'] = isset($data['site']) ? $data['site'] : null;
        $this->container['ip_address'] = isset($data['ip_address']) ? $data['ip_address'] : null;
        $this->container['useragent'] = isset($data['useragent']) ? $data['useragent'] : null;
        $this->container['country'] = isset($data['country']) ? $data['country'] : null;
        $this->container['device'] = isset($data['device']) ? $data['device'] : null;
        $this->container['os'] = isset($data['os']) ? $data['os'] : null;
        $this->container['attacked_url'] = isset($data['attacked_url']) ? $data['attacked_url'] : null;
        $this->container['attacked_parameter'] = isset($data['attacked_parameter']) ? $data['attacked_parameter'] : null;
        $this->container['attack_vector'] = isset($data['attack_vector']) ? $data['attack_vector'] : null;
        $this->container['blocking_status'] = isset($data['blocking_status']) ? $data['blocking_status'] : null;
        $this->container['raw_http_request'] = isset($data['raw_http_request']) ? $data['raw_http_request'] : null;
        $this->container['created_at'] = isset($data['created_at']) ? $data['created_at'] : null;
        $this->container['false_positive'] = isset($data['false_positive']) ? $data['false_positive'] : null;
        $this->container['waf_rule'] = isset($data['waf_rule']) ? $data['waf_rule'] : null;
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
     * Gets id.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->container['id'];
    }
    /**
     * Sets id.
     *
     * @param int|null $id id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->container['id'] = $id;
        return $this;
    }
    /**
     * Gets site.
     *
     * @return string|null
     */
    public function getSite()
    {
        return $this->container['site'];
    }
    /**
     * Sets site.
     *
     * @param string|null $site site
     *
     * @return $this
     */
    public function setSite($site)
    {
        $this->container['site'] = $site;
        return $this;
    }
    /**
     * Gets ip_address.
     *
     * @return string|null
     */
    public function getIpAddress()
    {
        return $this->container['ip_address'];
    }
    /**
     * Sets ip_address.
     *
     * @param string|null $ip_address ip_address
     *
     * @return $this
     */
    public function setIpAddress($ip_address)
    {
        $this->container['ip_address'] = $ip_address;
        return $this;
    }
    /**
     * Gets useragent.
     *
     * @return string|null
     */
    public function getUseragent()
    {
        return $this->container['useragent'];
    }
    /**
     * Sets useragent.
     *
     * @param string|null $useragent useragent
     *
     * @return $this
     */
    public function setUseragent($useragent)
    {
        $this->container['useragent'] = $useragent;
        return $this;
    }
    /**
     * Gets country.
     *
     * @return string|null
     */
    public function getCountry()
    {
        return $this->container['country'];
    }
    /**
     * Sets country.
     *
     * @param string|null $country country
     *
     * @return $this
     */
    public function setCountry($country)
    {
        $this->container['country'] = $country;
        return $this;
    }
    /**
     * Gets device.
     *
     * @return string|null
     */
    public function getDevice()
    {
        return $this->container['device'];
    }
    /**
     * Sets device.
     *
     * @param string|null $device device
     *
     * @return $this
     */
    public function setDevice($device)
    {
        $this->container['device'] = $device;
        return $this;
    }
    /**
     * Gets os.
     *
     * @return string|null
     */
    public function getOs()
    {
        return $this->container['os'];
    }
    /**
     * Sets os.
     *
     * @param string|null $os os
     *
     * @return $this
     */
    public function setOs($os)
    {
        $this->container['os'] = $os;
        return $this;
    }
    /**
     * Gets attacked_url.
     *
     * @return string|null
     */
    public function getAttackedUrl()
    {
        return $this->container['attacked_url'];
    }
    /**
     * Sets attacked_url.
     *
     * @param string|null $attacked_url attacked_url
     *
     * @return $this
     */
    public function setAttackedUrl($attacked_url)
    {
        $this->container['attacked_url'] = $attacked_url;
        return $this;
    }
    /**
     * Gets attacked_parameter.
     *
     * @return string|null
     */
    public function getAttackedParameter()
    {
        return $this->container['attacked_parameter'];
    }
    /**
     * Sets attacked_parameter.
     *
     * @param string|null $attacked_parameter attacked_parameter
     *
     * @return $this
     */
    public function setAttackedParameter($attacked_parameter)
    {
        $this->container['attacked_parameter'] = $attacked_parameter;
        return $this;
    }
    /**
     * Gets attack_vector.
     *
     * @return string|null
     */
    public function getAttackVector()
    {
        return $this->container['attack_vector'];
    }
    /**
     * Sets attack_vector.
     *
     * @param string|null $attack_vector attack_vector
     *
     * @return $this
     */
    public function setAttackVector($attack_vector)
    {
        $this->container['attack_vector'] = $attack_vector;
        return $this;
    }
    /**
     * Gets blocking_status.
     *
     * @return \GetAstra\Api\Client\Model\BlockingStatus|null
     */
    public function getBlockingStatus()
    {
        return $this->container['blocking_status'];
    }
    /**
     * Sets blocking_status.
     *
     * @param \GetAstra\Api\Client\Model\BlockingStatus|null $blocking_status blocking_status
     *
     * @return $this
     */
    public function setBlockingStatus($blocking_status)
    {
        $this->container['blocking_status'] = $blocking_status;
        return $this;
    }
    /**
     * Gets raw_http_request.
     *
     * @return string|null
     */
    public function getRawHttpRequest()
    {
        return $this->container['raw_http_request'];
    }
    /**
     * Sets raw_http_request.
     *
     * @param string|null $raw_http_request raw_http_request
     *
     * @return $this
     */
    public function setRawHttpRequest($raw_http_request)
    {
        $this->container['raw_http_request'] = $raw_http_request;
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
     * Gets false_positive.
     *
     * @return bool|null
     */
    public function getFalsePositive()
    {
        return $this->container['false_positive'];
    }
    /**
     * Sets false_positive.
     *
     * @param bool|null $false_positive false_positive
     *
     * @return $this
     */
    public function setFalsePositive($false_positive)
    {
        $this->container['false_positive'] = $false_positive;
        return $this;
    }
    /**
     * Gets waf_rule.
     *
     * @return string[]|null
     */
    public function getWafRule()
    {
        return $this->container['waf_rule'];
    }
    /**
     * Sets waf_rule.
     *
     * @param string[]|null $waf_rule waf_rule
     *
     * @return $this
     */
    public function setWafRule($waf_rule)
    {
        $this->container['waf_rule'] = $waf_rule;
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
