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

use Magento\Framework\Serialize\Serializer\Json;
use Mirasvit\Rewards\Api\Data\Earning\RuleInterface;
use Mirasvit\Rewards\Helper\Json as HelperJson;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\Rewards\Api\Data\TierInterface;

/**
 * @method \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\Collection getCollection()
 * @method \Mirasvit\Rewards\Model\Earning\Rule load(int $id)
 * @method bool getIsMassDelete()
 * @method \Mirasvit\Rewards\Model\Earning\Rule setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method \Mirasvit\Rewards\Model\Earning\Rule setIsMassStatus(bool $flag)
 * @method bool getActivatesAfterDays()()
 * @method \Mirasvit\Rewards\Model\Earning\Rule setActivatesAfterDays(int $days)
 * @method \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule getResource()
 */
class Rule extends \Magento\SalesRule\Model\Rule
{
    const TYPE_PRODUCT = 'product';
    const TYPE_CART = 'cart';
    const TYPE_BEHAVIOR = 'behavior';

    const CACHE_TAG = 'rewards_earning_rule';

    private $tierFactory;

    /**
     * @var HelperJson
     */
    private $helperJson;
    /**
     * @var \Mirasvit\Rewards\Helper\Serializer
     */
    private $serializerHelper;
    /**
     * @var Config\Source\Type
     */
    private $configSourceType;

    protected $_cacheTag = 'rewards_earning_rule';
    protected $_eventPrefix = 'rewards_earning_rule';
    /**
     * @var \Mirasvit\Rewards\Helper\Serializer|\Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;
    /**
     * @var Rule\Condition\CombineFactory
     */
    protected $earningRuleConditionCombineFactory;
    /**
     * @var Rule\Action\Product\CombineFactory
     */
    protected $ruleActionProductCombineFactory;
    /**
     * @var Rule\Action\CollectionFactory
     */
    protected $earningRuleActionCollectionFactory;
    /**
     * @var \Mirasvit\Rewards\Helper\Storeview
     */
    protected $rewardsStoreview;
    /**
     * @var \Magento\Framework\Model\Context
     */
    protected $context;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource
     */
    protected $resource;
    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb
     */
    protected $resourceCollection;

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
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        HelperJson $helperJson,
        \Mirasvit\Rewards\Model\Config\Source\Type $configSourceType,
        Rule\Condition\CombineFactory $earningRuleConditionCombineFactory,
        \Mirasvit\Rewards\Model\Earning\Rule\Action\Product\CombineFactory $ruleActionProductCombineFactory,
        Rule\Action\CollectionFactory $earningRuleActionCollectionFactory,
        \Mirasvit\Rewards\Helper\Serializer $serializerHelper,
        \Mirasvit\Rewards\Helper\Storeview $rewardsStoreview,
        \Mirasvit\Rewards\Model\Earning\TierFactory $tierFactory,
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
        $this->helperJson                         = $helperJson;
        $this->configSourceType                   = $configSourceType;
        $this->earningRuleConditionCombineFactory = $earningRuleConditionCombineFactory;
        $this->ruleActionProductCombineFactory    = $ruleActionProductCombineFactory;
        $this->earningRuleActionCollectionFactory = $earningRuleActionCollectionFactory;
        $this->serializerHelper                   = $serializerHelper;
        $this->rewardsStoreview                   = $rewardsStoreview;
        $this->tierFactory                        = $tierFactory;
        $this->context                            = $context;
        $this->registry                           = $registry;
        $this->resource                           = $resource;
        $this->resourceCollection                 = $resourceCollection;

        // for m2.1.x
        if (empty($this->serializer)) {
            /** @var Json $serializerHelper */
            $this->serializer = $serializerHelper;
        }

        parent::__construct($context, $registry, $formFactory, $localeDate, $couponFactory, $codegenFactory,
            $condCombineFactory, $condProdCombineF, $couponCollection, $storeManager, $resource, $resourceCollection,
            $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rewards\Model\ResourceModel\Earning\Rule');
    }

    /**
     * @param bool|false $emptyOption
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /**
     * @return Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        $combine = $this->earningRuleConditionCombineFactory->create();
        return $combine;
    }

    /**
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return $this->ruleActionProductCombineFactory->create();
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
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
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
     * @return bool|Json
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

    /**
     * @return string
     */
    public function getEmailMessage()
    {
        return $this->rewardsStoreview->getStoreViewValue($this, 'email_message');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setEmailMessage($value)
    {
        $this->rewardsStoreview->setStoreViewValue($this, 'email_message', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getHistoryMessage()
    {
        return $this->rewardsStoreview->getStoreViewValue($this, 'history_message');
    }

    /**
     * @param int|string $value
     * @return $this
     */
    public function setHistoryMessage($value)
    {
        $this->rewardsStoreview->setStoreViewValue($this, 'history_message', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getProductNotification()
    {
        return $this->rewardsStoreview->getStoreViewValue($this, 'product_notification');
    }

    /**
     * @param int|string $value
     * @return $this
     */
    public function setProductNotification($value)
    {
        $this->rewardsStoreview->setStoreViewValue($this, 'product_notification', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getFrontName()
    {
        return $this->rewardsStoreview->getStoreViewValue($this, 'front_name');
    }

    /**
     * @param int|string $value
     * @return $this
     */
    public function setFrontName($value)
    {
        $this->rewardsStoreview->setStoreViewValue($this, 'front_name', $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addData(array $data)
    {
        if (!empty($data['email_message']) && !$this->helperJson->isEncoded($data['email_message']) &&
            !$this->helperJson->isSerialized($data['email_message'])
        ) {
            $this->setEmailMessage($data['email_message']);
            unset($data['email_message']);
        }

        if (!empty($data['history_message']) && !$this->helperJson->isEncoded($data['history_message']) &&
            !$this->helperJson->isSerialized($data['history_message'])
        ) {
            $this->setHistoryMessage($data['history_message']);
            unset($data['history_message']);
        }

        if (!empty($data['front_name']) && !$this->helperJson->isEncoded($data['front_name']) &&
            !$this->helperJson->isSerialized($data['front_name'])
        ) {
            $this->setFrontName($data['front_name']);
            unset($data['front_name']);
        }

        if (!empty($data['product_notification']) && !$this->helperJson->isEncoded($data['product_notification']) &&
            !$this->helperJson->isSerialized($data['product_notification'])
        ) {
            $this->setProductNotification($data['product_notification']);
            unset($data['product_notification']);
        }

        if (isset($data['type'])) {
            $types = $this->configSourceType->toArray();
            if (!isset($types[$data['type']])) {
                unset($data['type']);
            }
        }

        return parent::addData($data);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyAll()
    {
        /** @var mixed $resource */
        $resource = $this->_getResource();
        $resource->applyAllRulesForDateRange();
    }

    /**
     * @return array
     */
    public function getWebsiteIds()
    {
        return $this->getData('website_ids');
    }

    /**
     * @return array
     */
    public function getTiersSerialized()
    {
        $result = [];
        $tiers = $this->getData(RuleInterface::KEY_TIERS_SERIALIZED);
        if ($tiers) {
            $result = $this->serializer->unserialize($tiers);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getDefaultTierData()
    {
        return [
            RuleInterface::KEY_TIER_KEY_EARNING_STYLE => Config::EARNING_STYLE_GIVE,
            RuleInterface::KEY_TIER_KEY_EARN_POINTS   => 0,
            RuleInterface::KEY_TIER_KEY_POINTS_LIMIT  => 0,
        ];
    }


    /**
     * @param \Magento\Customer\Model\Customer|\Magento\Framework\DataObject $customer
     * @return Tier
     */
    public function getTier($customer)
    {
        $currentTierId = (int)$customer->getData(TierInterface::CUSTOMER_KEY_TIER_ID);

        $tiers = $this->getTiersSerialized();
        if ($currentTierId) {
            if (isset($tiers[$currentTierId])) {
                $tierData = $tiers[$currentTierId];
            } else {
                $tierData = $this->getDefaultTierData();
            }
        } else {
            $tierData = array_shift($tiers);
        }

        return $this->tierFactory->create(['tierData' => $tierData]);
    }

    /**
     * {@inheritDoc}
     */
    public function validate(\Magento\Framework\DataObject $object)
    {
        \Magento\Framework\Profiler::start(__METHOD__);
        if ($this->getIsProductPage() && $this->getType() == self::TYPE_CART && $this->getIsShowOnProductPage()) {
            $object->setIsProductPage($this->getIsProductPage());
        }
        \Magento\Framework\Profiler::stop(__METHOD__);

        \Magento\Framework\Profiler::start(__METHOD__ . ' PARENT');
        $isValid = parent::validate($object);
        \Magento\Framework\Profiler::stop(__METHOD__ . ' PARENT');

        return $isValid;
    }
}
