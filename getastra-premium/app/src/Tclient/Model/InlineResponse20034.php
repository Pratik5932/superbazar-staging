<?php

/**
 * InlineResponse20034.
 *
 * PHP version 5
 *
 * @category Class
 *
 * @author   OpenAPI Generator team
 *
 * @see     https://openapi-generator.tech
 */
/**
 * Astra v2.
 *
 * APIs for Astra Security Suite
 *
 * The version of the OpenAPI document: 1.0.0
 *
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 4.3.1
 */
/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */
namespace AstraPrefixed\GetAstra\Client\Tclient\Model;

use ArrayAccess;
use AstraPrefixed\GetAstra\Client\Tclient\ObjectSerializer;
/**
 * InlineResponse20034 Class Doc Comment.
 *
 * @category Class
 *
 * @author   OpenAPI Generator team
 *
 * @see     https://openapi-generator.tech
 */
class InlineResponse20034 implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;
    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'inline_response_200_34';
    /**
     * Array of property to type mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $openAPITypes = ['hydramember' => '\\GetAstra\\Client\\Tclient\\Model\\VaptTagJsonldVaptTagOutput[]', 'hydratotal_items' => 'int', 'hydraview' => 'AstraPrefixed\\GetAstra\\Client\\Tclient\\Model\\InlineResponse200HydraView', 'hydrasearch' => 'AstraPrefixed\\GetAstra\\Client\\Tclient\\Model\\InlineResponse200HydraSearch'];
    /**
     * Array of property to format mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $openAPIFormats = ['hydramember' => null, 'hydratotal_items' => null, 'hydraview' => null, 'hydrasearch' => null];
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
    protected static $attributeMap = ['hydramember' => 'hydra:member', 'hydratotal_items' => 'hydra:totalItems', 'hydraview' => 'hydra:view', 'hydrasearch' => 'hydra:search'];
    /**
     * Array of attributes to setter functions (for deserialization of responses).
     *
     * @var string[]
     */
    protected static $setters = ['hydramember' => 'setHydramember', 'hydratotal_items' => 'setHydratotalItems', 'hydraview' => 'setHydraview', 'hydrasearch' => 'setHydrasearch'];
    /**
     * Array of attributes to getter functions (for serialization of requests).
     *
     * @var string[]
     */
    protected static $getters = ['hydramember' => 'getHydramember', 'hydratotal_items' => 'getHydratotalItems', 'hydraview' => 'getHydraview', 'hydrasearch' => 'getHydrasearch'];
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
        $this->container['hydramember'] = isset($data['hydramember']) ? $data['hydramember'] : null;
        $this->container['hydratotal_items'] = isset($data['hydratotal_items']) ? $data['hydratotal_items'] : null;
        $this->container['hydraview'] = isset($data['hydraview']) ? $data['hydraview'] : null;
        $this->container['hydrasearch'] = isset($data['hydrasearch']) ? $data['hydrasearch'] : null;
    }
    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];
        if (null === $this->container['hydramember']) {
            $invalidProperties[] = "'hydramember' can't be null";
        }
        if (!\is_null($this->container['hydratotal_items']) && $this->container['hydratotal_items'] < 0) {
            $invalidProperties[] = "invalid value for 'hydratotal_items', must be bigger than or equal to 0.";
        }
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
     * Gets hydramember.
     *
     * @return \GetAstra\Client\Tclient\Model\VaptTagJsonldVaptTagOutput[]
     */
    public function getHydramember()
    {
        return $this->container['hydramember'];
    }
    /**
     * Sets hydramember.
     *
     * @param \GetAstra\Client\Tclient\Model\VaptTagJsonldVaptTagOutput[] $hydramember hydramember
     *
     * @return $this
     */
    public function setHydramember($hydramember)
    {
        $this->container['hydramember'] = $hydramember;
        return $this;
    }
    /**
     * Gets hydratotal_items.
     *
     * @return int|null
     */
    public function getHydratotalItems()
    {
        return $this->container['hydratotal_items'];
    }
    /**
     * Sets hydratotal_items.
     *
     * @param int|null $hydratotal_items hydratotal_items
     *
     * @return $this
     */
    public function setHydratotalItems($hydratotal_items)
    {
        if (!\is_null($hydratotal_items) && $hydratotal_items < 0) {
            throw new \InvalidArgumentException('invalid value for $hydratotal_items when calling InlineResponse20034., must be bigger than or equal to 0.');
        }
        $this->container['hydratotal_items'] = $hydratotal_items;
        return $this;
    }
    /**
     * Gets hydraview.
     *
     * @return \GetAstra\Client\Tclient\Model\InlineResponse200HydraView|null
     */
    public function getHydraview()
    {
        return $this->container['hydraview'];
    }
    /**
     * Sets hydraview.
     *
     * @param \GetAstra\Client\Tclient\Model\InlineResponse200HydraView|null $hydraview hydraview
     *
     * @return $this
     */
    public function setHydraview($hydraview)
    {
        $this->container['hydraview'] = $hydraview;
        return $this;
    }
    /**
     * Gets hydrasearch.
     *
     * @return \GetAstra\Client\Tclient\Model\InlineResponse200HydraSearch|null
     */
    public function getHydrasearch()
    {
        return $this->container['hydrasearch'];
    }
    /**
     * Sets hydrasearch.
     *
     * @param \GetAstra\Client\Tclient\Model\InlineResponse200HydraSearch|null $hydrasearch hydrasearch
     *
     * @return $this
     */
    public function setHydrasearch($hydrasearch)
    {
        $this->container['hydrasearch'] = $hydrasearch;
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
