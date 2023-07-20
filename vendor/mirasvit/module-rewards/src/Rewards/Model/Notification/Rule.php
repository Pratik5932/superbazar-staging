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



namespace Mirasvit\Rewards\Model\Notification;

use Mirasvit\Rewards\Api\Data\Notification\RuleInterface;
use Mirasvit\Rewards\Helper\Json as JsonHelper;
use Mirasvit\Rewards\Helper\Rule as RuleHelper;
use Mirasvit\Rewards\Model as Model;

/**
 * @method \Mirasvit\Rewards\Model\ResourceModel\Notification\Rule\Collection getCollection()
 * @method \Mirasvit\Rewards\Model\Notification\Rule load(int $id)
 * @method bool getIsMassDelete()
 * @method \Mirasvit\Rewards\Model\Notification\Rule setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method \Mirasvit\Rewards\Model\Notification\Rule setIsMassStatus(bool $flag)
 * @method \Mirasvit\Rewards\Model\ResourceModel\Notification\Rule getResource()
 */
class Rule extends \Magento\SalesRule\Model\Rule
{
    const TYPE_PRODUCT = 'product';
    const TYPE_CART = 'cart';
    const TYPE_CUSTOM = 'custom';

    const CACHE_TAG = 'rewards_notification_rule';

    /**
     * @var string
     */
    protected $_cacheTag = 'rewards_notification_rule';
    /**
     * @var string
     */
    protected $_eventPrefix = 'rewards_notification_rule';

    /**
     * @var \Mirasvit\Rewards\Helper\Storeview
     */
    private $rewardsStoreview;
    /**
     * @var RuleHelper
     */
    private $ruleHelper;
    /**
     * @var JsonHelper
     */
    private $jsonHelper;
    /**
     * @var Rule\Condition\CombineFactory
     */
    private $notificationRuleConditionCombineFactory;
    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory
     */
    private $ruleConditionProductCombineFactory;
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    public function __construct(
        RuleHelper $ruleHelper,
        JsonHelper $jsonHelper,
        Model\Notification\Rule\Condition\CombineFactory $notificationRuleConditionCombineFactory,
        \Mirasvit\Rewards\Helper\Storeview $rewardsStoreview,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $ruleConditionProductCombineFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\Coupon\CodegeneratorFactory $codegenFactory,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\SalesRule\Model\ResourceModel\Coupon\Collection $couponCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->rewardsStoreview                        = $rewardsStoreview;
        $this->ruleHelper                              = $ruleHelper;
        $this->jsonHelper                              = $jsonHelper;
        $this->notificationRuleConditionCombineFactory = $notificationRuleConditionCombineFactory;
        $this->ruleConditionProductCombineFactory      = $ruleConditionProductCombineFactory;
        $this->appState                                = $context->getAppState();

        parent::__construct($context, $registry, $formFactory, $localeDate, $couponFactory, $codegenFactory,
            $condCombineFactory, $condProdCombineF, $couponCollection, $storeManager, $resource, $resourceCollection,
            $data);
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
        $this->_init('Mirasvit\Rewards\Model\ResourceModel\Notification\Rule');
        $this->setIdFieldName('notification_rule_id');
    }

    /**
     * @param bool|false $emptyOption
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /** Rule Methods **/
    /**
     * @return \Mirasvit\Rewards\Model\Notification\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->notificationRuleConditionCombineFactory->create();
    }

    /**
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return $this->ruleConditionProductCombineFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getConditions()
    {
        $condition = null;
        try {
            $condition = parent::getConditions();
        } catch (\Exception $e) {
            if ($serializeObj = $this->getSerializer()) {
                $origin = clone $this->serializer;
                $this->serializer = $serializeObj;
                $condition = parent::getConditions();
                $this->serializer = $origin;
            }
        }

        return $condition;
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        $action = null;
        try {
            $action = parent::getActions();
        } catch (\Exception $e) {
            if ($serializeObj = $this->getSerializer()) {
                $origin = clone $this->serializer;
                $this->serializer = $serializeObj;
                $action = parent::getActions();
                $this->serializer = $origin;
            }
        }

        return $action;
    }

    /**
     * @return bool|\Magento\Framework\Serialize\Serializer\Json
     */
    protected function getSerializer()
    {
        $serializer = false;
        if (class_exists(\Magento\Framework\Serialize\Serializer\Serialize::class)) {
            $serializer = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Serialize\Serializer\Serialize::class
            );
        }

        return $serializer;

    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductIds()
    {
        return $this->_getResource()->getRuleProductIds($this->getId());
    }

    /**
     * @param string $format
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toString($format = '')
    {
        $this->load($this->getId());
        $string = $this->getConditions()->asStringRecursive();

        $string = nl2br(preg_replace('/ /', '&nbsp;', $string));

        return $string;
    }
    /************************/

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyAll()
    {
        $this->_getResource()->applyAllRulesForDateRange();
    }

    /**
     * @return array
     */
    public function getWebsiteIds()
    {
        return $this->getData('website_ids');
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        if (is_array($this->getData('type'))) {
            $this->setData('type', implode(',', $this->getData('type')));
        }
        parent::beforeSave();
    }

    /**
     * @return array
     */
    public function getType()
    {
        $type = parent::getType();
        if (is_string($type)) {
            return explode(',', $type);
        }

        return $type;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        $message =  $this->rewardsStoreview->getStoreViewValue($this, RuleInterface::KEY_MESSAGE);
        if ($this->appState->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            return $message;
        }

        return $this->ruleHelper->replaceCurrencyVariable($message);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setMessage($value)
    {
        $this->rewardsStoreview->setStoreViewValue($this, RuleInterface::KEY_MESSAGE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addData(array $data)
    {
        if (!empty($data[RuleInterface::KEY_MESSAGE]) &&
            !$this->jsonHelper->isEncoded($data[RuleInterface::KEY_MESSAGE]) &&
            !$this->jsonHelper->isSerialized($data[RuleInterface::KEY_MESSAGE])
        ) {
            $this->setMessage($data[RuleInterface::KEY_MESSAGE]);
            unset($data[RuleInterface::KEY_MESSAGE]);
        }

        return parent::addData($data);
    }
}
