<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mppaypalexpresscheckout
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mppaypalexpresscheckout\Model;

use Webkul\Mppaypalexpresscheckout\Api\Data\MppaypalexpresscheckoutInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Mppaypalexpresscheckout Model
 */
class Mppaypalexpresscheckout extends \Magento\Framework\Model\AbstractModel implements
    MppaypalexpresscheckoutInterface,
    IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**#@+
     * Product's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Marketplace Mppaypalexpresscheckout cache tag
     */
    const CACHE_TAG = 'marketplace_mppaypalexpresscheckout';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_mppaypalexpresscheckout';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_mppaypalexpresscheckout';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Webkul\Mppaypalexpresscheckout\Model\ResourceModel\Mppaypalexpresscheckout'
        );
    }

    /**
     * Load object data
     *
     * @param  int|null $id
     * @param  string   $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteMppaypalexpresscheckout();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Mppaypalexpresscheckout
     *
     * @return \Webkul\Mppaypalexpresscheckout\Model\Mppaypalexpresscheckout
     */
    public function noRouteMppaypalexpresscheckout()
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
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID
     *
     * @param  int $id
     * @return \Webkul\Mppaypalexpresscheckout\Api\Data\MppaypalexpresscheckoutInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Prepare expresschekout detail's statuses.
     * Available event marketplace_mppaypalexpresscheckout_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Approved'),
            self::STATUS_DISABLED => __('Disapproved'),
        ];
    }
}
