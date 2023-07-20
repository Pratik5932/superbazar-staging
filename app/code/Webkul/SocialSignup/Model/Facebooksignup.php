<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Model;

use Webkul\SocialSignup\Api\Data\FacebooksignupInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Facebooksignup Model.
 *
 * @method \Webkul\MpSellerGroup\Model\ResourceModel\SellerGroup _getResource()
 * @method \Webkul\MpSellerGroup\Model\ResourceModel\SellerGroup getResource()
 */
class Facebooksignup extends \Magento\Framework\Model\AbstractModel implements
    FacebooksignupInterface,
    IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**#@+
     * Feedback's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Social singup cache tag.
     */
    const CACHE_TAG = 'wk_facebook_customer';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_facebook_customer';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_facebook_customer';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\SocialSignup\Model\ResourceModel\Facebooksignup::class);
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteSellerGroup();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route facebooksignup.
     *
     * @return \Webkul\SocialSignup\Model\Facebooksignup
     */
    public function noRouteSellerGroup()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\SocialSignup\Api\Data\SocialSignupInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Facebook Id.
     *
     * @return int|null
     */
    public function getFbId()
    {
        return $this->_getData(self::FB_ID);
    }
    /**
     * Get CustomerId Code.
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->_getData(self::CUSTOMER_ID);
    }
}
