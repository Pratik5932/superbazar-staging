<?php
namespace Superbazaar\CustomWork\Api\Data;

interface UserAgentInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID              = 'id';
    /**#@-*/

    const USERAGENT       = 'useragent';

    const ZIPCODE         = 'zipcode';
    
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
     * @return \Superbazaar\CustomWork\Api\Data\UserAgentInterface
     */
    public function setId($id);

    /**
     * Get useragent
     *
     * @return string|null
     */
    public function getUseragent();

    /**
     * Set useragent
     *
     * @param string $useragent
     * @return \Superbazaar\CustomWork\Api\Data\UserAgentInterface
     */
    public function setUseragent($useragent);

    /**
     * Get Zipcode
     *
     * @return string|null
     */
    public function getZipcode();

    /**
     * Set ZipCode
     *
     * @param string $zipcode
     * @return \Superbazaar\CustomWork\Api\Data\UserAgentInterface
     */
    public function setZipcode($zipcode);

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
     * @return \Superbazaar\CustomWork\Api\Data\UserAgentInterface
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
     * @return \Superbazaar\CustomWork\Api\Data\UserAgentInterface
     */
    public function setUpdatedAt($updatedAt);
}
