<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ZipCodeValidator
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ZipCodeValidator\Api\Data;

/**
 * Zipcode Validator Zipcode interface.
 * @api
 */
interface ZipcodeInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID              = 'id';
    /**#@-*/

    const REGION_ID       = 'region_id';

    const REGION_ZIPCODE  = 'region_zipcode';
    
    const CREATED_AT      = 'created_at';
    
    const UPDATED_AT      = 'updated_at';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\MpZipCodeValidator\Api\Data\ZipcodeInterface
     */
    public function setId($id);

    /**
     * Get Region Id
     *
     * @return int|null
     */
    public function getRegionId();

    /**
     * Set Region Id
     *
     * @param int $regionId
     * @return \Webkul\MpZipCodeValidator\Api\Data\ZipcodeInterface
     */
    public function setRegionId($regionId);

    /**
     * Get Region Zipcode
     *
     * @return int|null
     */
    public function getRegionZipcode();

    /**
     * Set Region ZipCode
     *
     * @param int $regionZipcode
     * @return \Webkul\MpZipCodeValidator\Api\Data\ZipcodeInterface
     */
    public function setRegionZipcode($regionZipcode);

    /**
     * Get Created Time
     *
     * @return int|null
     */
    public function getCreatedAt();

    /**
     * Set Created Time
     *
     * @param int $createdAt
     * @return \Webkul\MpZipCodeValidator\Api\Data\ZipcodeInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Updated Time
     *
     * @return int|null
     */
    public function getUpdatedAt();

    /**
     * Set Updated Time
     *
     * @param int $updatedAt
     * @return \Webkul\MpZipCodeValidator\Api\Data\ZipcodeInterface
     */
    public function setUpdatedAt($updatedAt);
}
