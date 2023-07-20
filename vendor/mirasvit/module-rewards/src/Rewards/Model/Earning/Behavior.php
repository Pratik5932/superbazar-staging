<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   3.0.57
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rewards\Model\Earning;

use Magento\Framework\DataObject\IdentityInterface;
use Mirasvit\Rewards\Api\Data\Earning\RuleInterface;
use Mirasvit\Rewards\Helper\Json as HelperJson;

/**
 * @method \Mirasvit\Rewards\Model\ResourceModel\Earning\Behavior\Collection getCollection()
 * @method \Mirasvit\Rewards\Model\Earning\Behavior load(int $id)
 * @method bool getIsMassDelete()
 * @method \Mirasvit\Rewards\Model\Earning\Behavior setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method \Mirasvit\Rewards\Model\Earning\Behavior setIsMassStatus(bool $flag)
 * @method \Mirasvit\Rewards\Model\ResourceModel\Earning\Behavior getResource()
 */
class Behavior extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'rewards_earning_behavior';

    /**
     * @var HelperJson
     */
    private $helperJson;
    /**
     * @var \Mirasvit\Rewards\Helper\Storeview
     */
    private $rewardsStoreview;

    protected $_cacheTag = 'rewards_earning_behavior';
    protected $_eventPrefix = 'rewards_earning_behavior';

    public function __construct(
        HelperJson $helperJson,
        \Mirasvit\Rewards\Helper\Storeview $rewardsStoreview,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->helperJson = $helperJson;
        $this->rewardsStoreview = $rewardsStoreview;
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
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rewards\Model\ResourceModel\Earning\Behavior');
    }

    /**
     * @param bool|false $emptyOption
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /************************/

    /**
     * @param string $code
     * @return bool|Behavior
     */
    public function getByActionCode($code)
    {
        $instance = $this->getCollection()
            ->addFieldToFilter('action_code', $code)
            ->addFieldToFilter('is_active', 1)
            ->getFirstItem();

        if ($instance->getId()) {
            return $instance;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getEmailMessage()
    {
        return $this->rewardsStoreview->getStoreViewValue($this, RuleInterface::KEY_EMAIL_MESSAGE);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setEmailMessage($value)
    {
        $this->rewardsStoreview->setStoreViewValue($this, RuleInterface::KEY_EMAIL_MESSAGE, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getHistoryMessage()
    {
        return $this->rewardsStoreview->getStoreViewValue($this, RuleInterface::KEY_HISTORY_MESSAGE);
    }

    /**
     * @param int|string $value
     * @return $this
     */
    public function setHistoryMessage($value)
    {
        $this->rewardsStoreview->setStoreViewValue($this, RuleInterface::KEY_HISTORY_MESSAGE, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getFrontName()
    {
        return $this->rewardsStoreview->getStoreViewValue($this, RuleInterface::KEY_FRONT_NAME);
    }

    /**
     * @param int|string $value
     * @return $this
     */
    public function setFrontName($value)
    {
        $this->rewardsStoreview->setStoreViewValue($this, RuleInterface::KEY_FRONT_NAME, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addData(array $data)
    {
        if (!empty($data[RuleInterface::KEY_EMAIL_MESSAGE]) &&
            !$this->helperJson->isEncoded($data[RuleInterface::KEY_EMAIL_MESSAGE]) &&
            !$this->helperJson->isSerialized($data[RuleInterface::KEY_EMAIL_MESSAGE])
        ) {
            $this->setEmailMessage($data[RuleInterface::KEY_EMAIL_MESSAGE]);
            unset($data[RuleInterface::KEY_EMAIL_MESSAGE]);
        }

        if (!empty($data[RuleInterface::KEY_HISTORY_MESSAGE]) &&
            !$this->helperJson->isEncoded($data[RuleInterface::KEY_HISTORY_MESSAGE]) &&
            !$this->helperJson->isSerialized($data[RuleInterface::KEY_HISTORY_MESSAGE])
        ) {
            $this->setHistoryMessage($data[RuleInterface::KEY_HISTORY_MESSAGE]);
            unset($data[RuleInterface::KEY_HISTORY_MESSAGE]);
        }

        if (!empty($data[RuleInterface::KEY_FRONT_NAME]) &&
            !$this->helperJson->isEncoded($data[RuleInterface::KEY_FRONT_NAME]) &&
            !$this->helperJson->isSerialized($data[RuleInterface::KEY_FRONT_NAME])
        ) {
            $this->setFrontName($data[RuleInterface::KEY_FRONT_NAME]);
            unset($data[RuleInterface::KEY_FRONT_NAME]);
        }

        return parent::addData($data);
    }
}
