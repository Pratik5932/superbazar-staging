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


namespace Mirasvit\Rewards\Model\Spending;

use Magento\Framework\Exception\LocalizedException;
use Mirasvit\Rewards\Api\Config\Rule\SpendingStyleInterface;
use Mirasvit\Rewards\Api\Data\Spending\RuleInterface;
use Mirasvit\Rewards\Api\Data\TierInterface;
use Mirasvit\Rewards\Helper\Json as HelperJson;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * @method \Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\Collection getCollection()
 * @method \Mirasvit\Rewards\Model\Spending\Rule load(int $id)
 * @method bool getIsMassDelete()
 * @method \Mirasvit\Rewards\Model\Spending\Rule setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method \Mirasvit\Rewards\Model\Spending\Rule setIsMassStatus(bool $flag)
 * @method \Mirasvit\Rewards\Model\ResourceModel\Spending\Rule getResource()
 */
class Rule extends \Magento\SalesRule\Model\Rule
{
    const TYPE_PRODUCT = 'product';
    const TYPE_CART = 'cart';
    const TYPE_CUSTOM = 'custom';

    const CACHE_TAG = 'rewards_spending_rule';

    private $tierFactory;

    protected $_cacheTag = 'rewards_spending_rule';
    protected $_eventPrefix = 'rewards_spending_rule';

    /**
     * @var HelperJson
     */
    private $helperJson;
    /**
     * @var Rule\Condition\CombineFactory
     */
    private $spendingRuleConditionCombineFactory;
    /**
     * @var Rule\Action\CollectionFactory
     */
    private $spendingRuleActionCollectionFactory;
    /**
     * @var Rule\Condition\Product\CombineFactory
     */
    private $ruleConditionProductCombineFactory;
    /**
     * @var \Mirasvit\Rewards\Service\RoundService
     */
    private $roundService;
    /**
     * @var \Magento\Framework\Model\Context
     */
    private $context;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource
     */
    private $resource;
    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb
     */
    private $resourceCollection;
    /**
     * @var \Mirasvit\Rewards\Helper\Storeview
     */
    private $rewardsStoreview;

    private $customerFactory;

    private $productMetadata;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ProductMetadataInterface $productMetadata,
        HelperJson $helperJson,
        \Mirasvit\Rewards\Helper\Storeview $rewardsStoreview,
        Rule\Condition\CombineFactory $spendingRuleConditionCombineFactory,
        Rule\Action\CollectionFactory $spendingRuleActionCollectionFactory,
        \Mirasvit\Rewards\Model\Spending\TierFactory $tierFactory,
        \Mirasvit\Rewards\Model\Spending\Rule\Condition\Product\CombineFactory $ruleConditionProductCombineFactory,
        \Mirasvit\Rewards\Service\RoundService $roundService,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
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
        $this->productMetadata                     = $productMetadata;
        $this->helperJson                          = $helperJson;
        $this->rewardsStoreview                    = $rewardsStoreview;
        $this->spendingRuleConditionCombineFactory = $spendingRuleConditionCombineFactory;
        $this->spendingRuleActionCollectionFactory = $spendingRuleActionCollectionFactory;
        $this->tierFactory                         = $tierFactory;
        $this->ruleConditionProductCombineFactory  = $ruleConditionProductCombineFactory;
        $this->roundService                        = $roundService;
        $this->customerFactory                     = $customerFactory;
        $this->context                             = $context;
        $this->registry                            = $registry;
        $this->resource                            = $resource;
        $this->resourceCollection                  = $resourceCollection;

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
        $this->_init('Mirasvit\Rewards\Model\ResourceModel\Spending\Rule');
        $this->setIdFieldName('spending_rule_id');
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
     * @param bool|false $emptyOption
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /** Rule Methods **/
    /**
     * @return Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->spendingRuleConditionCombineFactory->create();
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

    /**
     * @return array
     */
    public function getTiersSerialized()
    {
        $result = [];
        $tiers = $this->getData(RuleInterface::KEY_TIERS_SERIALIZED);
        if ($tiers) {
            $result = $this->helperJson->unserialize($tiers);
        }

        return $result;
    }

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
     * @param \Magento\Customer\Model\Customer|\Magento\Customer\Api\Data\CustomerInterface $customer
     * @return Tier
     */
    public function getTier($customer)
    {
        // compatibility with m2.1.0
        if (!method_exists($customer, 'getAttributes')) {
            $customerId = $customer->getId();
            $customer = $this->customerFactory->create();
            $customer->getResource()->load($customer, $customerId);
        }

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

        return $this->tierFactory->create([
            'roundService' => $this->roundService,
            'tierData'     => $tierData
        ]);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function validateTierFields()
    {
        $tiersData = $this->getTiersSerialized();
        foreach ($tiersData as $tier) {
            $max = $tier[RuleInterface::KEY_TIER_KEY_SPEND_MAX_POINTS];
            $min = $tier[RuleInterface::KEY_TIER_KEY_SPEND_MIN_POINTS];
            // skip validation if one of values contains %
            if ((strpos($max, '%') === false && strpos($min, '%') !== false) ||
                (strpos($min, '%') === false && strpos($max, '%') !== false)
            ) {

            } else {
                if ($max && $min >= $max) {
                    throw new LocalizedException(
                        __("Field 'Spend maximum' can not be less or equal then field 'Spend minimum'")
                    );
                }
            }
            $spendPoints = $tier[RuleInterface::KEY_TIER_KEY_SPEND_POINTS];
            if (preg_match('/[^0-9]/', $spendPoints)) {
                throw new LocalizedException(
                    __("Field 'For each spent X points' should be integer")
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addData(array $data)
    {
        if (!empty($data['front_name']) && !$this->helperJson->isEncoded($data['front_name']) &&
            !$this->helperJson->isSerialized($data['front_name'])
        ) {
            $this->setFrontName($data['front_name']);
            unset($data['front_name']);
        }

        return parent::addData($data);
    }

    /**
     * @return array
     */
    public function getDefaultTierData()
    {
        return [
            RuleInterface::KEY_TIER_KEY_SPENDING_STYLE   => SpendingStyleInterface::STYLE_PARTIAL,
            RuleInterface::KEY_TIER_KEY_MONETARY_STEP    => 0,
            RuleInterface::KEY_TIER_KEY_SPEND_POINTS     => 1,
            RuleInterface::KEY_TIER_KEY_SPEND_MAX_POINTS => 0,
            RuleInterface::KEY_TIER_KEY_SPEND_MIN_POINTS => 0,
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave()
    {
        $parent = get_parent_class(get_parent_class(get_class($this)));

        while ($parent != "Magento\Rule\Model\AbstractModel") {
            $parent = get_parent_class($parent);
        }

        return $parent::afterSave();
    }
}
