<?php
namespace Superbazaar\CustomWork\Model;

use Superbazaar\CustomWork\Api\Data\UserAgentInterface;

class UserAgent extends \Magento\Framework\Model\AbstractModel implements UserAgentInterface
{
        /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Test Record cache tag
     */
    const CACHE_TAG = 'hyperlocal_useragent_list';

    /**
     * @var string
     */
    protected $_cacheTag = 'hyperlocal_useragent_list';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'hyperlocal_useragent_list';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Superbazaar\CustomWork\Model\ResourceModel\UserAgent');
    }

    /**
     * Load object data
     *
     * @param int|null $id
     * @param string $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteRegion();
        }
        return parent::load($id, $field);
    }
    public function noRouteRegion()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }
    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }
    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Superbazaar\CustomWork\Api\Data\UserAgentInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get useragent
     *
     * @return string|null
     */
    public function getUseragent()
	{
        return parent::getData(self::USERAGENT);
    }

    /**
     * Set useragent
     *
     * @param string $useragent
     * @return \Superbazaar\CustomWork\Api\Data\UserAgentInterface
     */
    public function setUseragent($useragent)
	{
        return $this->setData(self::USERAGENT, $useragent);
    }

    /**
     * Get Zipcode
     *
     * @return string|null
     */
    public function getZipcode()
	{
        return parent::getData(self::ZIPCODE);
    }

    /**
     * Set ZipCode
     *
     * @param string $zipcode
     * @return \Superbazaar\CustomWork\Api\Data\UserAgentInterface
     */
    public function setZipcode($zipcode)
	{
        return $this->setData(self::ZIPCODE, $zipcode);
    }

    /**
     * Get Created Time
     *
     * @return int|null
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * Set Created Time
     *
     * @param int $createdAt
     * @return \Superbazaar\CustomWork\Api\Data\UserAgentInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get Updated Time
     *
     * @return int|null
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }

    /**
     * Set Updated Time
     *
     * @param int $updatedAt
     * @return \Superbazaar\CustomWork\Api\Data\UserAgentInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
