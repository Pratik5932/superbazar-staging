<?php
namespace Mexbs\AdditionalPromotions\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\NoSuchEntityException;

class Data extends AbstractHelper{
    const RULE_POPUP_DISPLAYED_COOKIE_NAME = 'rule_popup_displayed';

    protected $objectManager;
    protected $ruleCollectionFactory;
    protected $orderConfig;
    protected $validator;
    protected $cmsContentFilter;
    protected $validatorUtility;
    protected $calculatorFactory;
    protected $currencyFactory;
    protected $storeManager;
    protected $eventManager;
    protected $directoryHelper;
    protected $usageFactory;
    protected $couponFactory;
    protected $customerFactory;
    protected $objectFactory;
    protected $productCollectionFactory;
    protected $productFactory;
    protected $resourceIterator;
    protected $connection;
    protected $serializer;
    protected $productMetaData;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\Cms\Model\Template\Filter $cmsContentFilter,
        \Magento\SalesRule\Model\Utility $validatorUtility,
        \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $calculatorFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\SalesRule\Model\ResourceModel\Coupon\UsageFactory $usageFactory,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\Rule\CustomerFactory $customerFactory,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer,
        \Magento\Framework\App\ProductMetadataInterface $productMetaData,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->objectManager = $objectManager;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->orderConfig = $orderConfig;
        $this->validator = $validator;
        $this->validatorUtility = $validatorUtility;
        $this->calculatorFactory = $calculatorFactory;
        $this->cmsContentFilter = $cmsContentFilter;
        $this->currencyFactory = $currencyFactory;
        $this->storeManager = $storeManager;
        $this->eventManager = $context->getEventManager();
        $this->directoryHelper = $directoryHelper;
        $this->usageFactory = $usageFactory;
        $this->couponFactory = $couponFactory;
        $this->customerFactory = $customerFactory;
        $this->objectFactory = $objectFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productFactory = $productFactory;
        $this->resourceIterator = $resourceIterator;
        $this->connection = $resource->getConnection();
        $this->serializer = $serializer;
        $this->productMetaData = $productMetaData;
        parent::__construct($context);
    }

    public function getMagentoVersion(){
        return $this->productMetaData->getVersion();
    }

    public $promoBlockSupportedSimpleActions = [
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\ProductsSetPercentDiscount::SIMPLE_ACTION,
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\ProductsSetFixedDiscount::SIMPLE_ACTION,
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\ProductsSetFixedPriceDiscount::SIMPLE_ACTION
    ];

    public $apSimpleActionsToTypes = [
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetYForEachXSpent::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetYForEachXSpent",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetYForEachXSpentUpToN::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetYForEachXSpentUpToN",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyXGetNOfYPercentDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyXGetNOfYPercentDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyXGetNOfYFixedDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyXGetNOfYFixedDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyXGetNOfYFixedPriceDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyXGetNOfYFixedPriceDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyABCGetNOfDPercentDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyABCGetNOfDPercentDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyABCGetNOfDFixedDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyABCGetNOfDFixedDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyABCGetNOfDFixedPriceDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyABCGetNOfDFixedPriceDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetEachNAfterMPercentDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetEachNAfterMPercentDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetEachNAfterMFixedDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetEachNAfterMFixedDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetEachNAfterMFixedPriceDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetEachNAfterMFixedPriceDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetAllAfterMPercentDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetAllAfterMPercentDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetAllAfterMFixedDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetAllAfterMFixedDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetAllAfterMFixedPriceDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetAllAfterMFixedPriceDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\EachGroupOfNPercentDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\EachGroupOfNPercentDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\EachGroupOfNFixedDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\EachGroupOfNFixedDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\EachGroupOfNFixedPriceDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\EachGroupOfNFixedPriceDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\ProductsSetPercentDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\ProductsSetPercentDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\ProductsSetFixedDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\ProductsSetFixedDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\ProductsSetFixedPriceDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\ProductsSetFixedPriceDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\FirstNNextMAfterKPercentDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\FirstNNextMAfterKPercentDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\FirstNNextMAfterKFixedDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\FirstNNextMAfterKFixedDiscount",
        \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\FirstNNextMAfterKFixedPriceDiscount::SIMPLE_ACTION =>
        "\Mexbs\AdditionalPromotions\Model\Rule\Action\Details\FirstNNextMAfterKFixedPriceDiscount"
    ];

    public function getSalesRulePopupImageUrl($imageName){
        $url = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'additional_promotions/sales_rule/' . $imageName;
        return $url;
    }

    public function validateActionDetail($actionDetail, $item){
        if ($actionDetail->validate($item)) {
            return true;
        }

        if($item->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
            $childItems = $item->getChildren();
            if (!empty($childItems)) {
                foreach ($childItems as $childItem) {
                    if ($actionDetail->validate($childItem)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function isRuleApAndFixedPrice($rule){
        try{
            $isSimpleActionAp = $this->isSimpleActionAp($rule->getSimpleAction());
            if(!$isSimpleActionAp){
                return false;
            }
            $actionDetailModel = $this->getLoadedActionDetail($rule);
            return (($actionDetailModel instanceof \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine)
                && ($actionDetailModel->getDiscountType() == \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine::DISCOUNT_TYPE_FIXED_PRICE));
        }catch(\Mexbs\AdditionalPromotions\SimpleActionNotFoundException $e){
            return false;
        }
    }

    public function shouldSkipTierPrice($rule){
        if($rule->getSkipTierPrice() == \Mexbs\AdditionalPromotions\Model\Source\SalesRule\YesNoConfig::CONFIG){
            return $this->getConfigIsSkipTierPrice();
        }
        return $rule->getSkipTierPrice();
    }

    public function shouldSkipSpecialPrice($rule){
        if($rule->getSkipSpecialPrice() == \Mexbs\AdditionalPromotions\Model\Source\SalesRule\YesNoConfig::CONFIG){
            return $this->getConfigIsSkipSpecialPrice();
        }
        return $rule->getSkipSpecialPrice();
    }

    public function getSpecialPrice($product, $originalPrice){
        return $product->getPriceModel()->calculateSpecialPrice(
            $originalPrice,
            $product->getSpecialPrice(),
            $product->getSpecialFromDate(),
            $product->getSpecialToDate(),
            $product->getStore()
        );
    }

    public function getCatalogRulePrice($product){
        return $product->getApFinalPriceAfterCatalogRules();
    }

    public function getOrderStatusesOptionArray(){
        $statuses = $this->orderConfig->getStatuses();
        $statusesOptionArray = [];

        foreach ($statuses as $code => $label) {
            $statusesOptionArray[] = ['value' => $code, 'label' => $label];
        }

        return $statusesOptionArray;
    }

    public function getLoadedActionDetail($rule){
        $simpleAction = $rule->getSimpleAction();
        if(!$this->isSimpleActionAp($simpleAction)){
            return null;
        }
        $type = $this->getSimpleActionType($simpleAction);

        if ($rule->hasActionDetailsSerialized()) {
            $actionDetails = $rule->getActionDetailsSerialized();
            if (!empty($actionDetails)) {
                $actionDetails = $this->serializer->unserialize($actionDetails);
                if (is_array($actionDetails) && !empty($actionDetails)) {
                    return $this->getLoadedActionDetailByArrayAndType($type, $rule, $actionDetails);
                }
            }
        }
        return $this->getLoadedActionDetailByArrayAndType($type, $rule, []);
    }

    public function isSimpleActionAp($simpleAction){
        try{
            $this->getSimpleActionType($simpleAction);
            return true;
        }catch(\Mexbs\AdditionalPromotions\SimpleActionNotFoundException $e){
            return false;
        }
    }

    public function isSimpleActionSupportsPromoBlock($simpleAction){
        return in_array($simpleAction, $this->promoBlockSupportedSimpleActions);
    }

    public function getLoadedActionDetailByArrayAndType($type, $rule, $actionDetailArr){
        $actionDetailModel = $this->objectManager->create($type)
            ->setRule($rule)
            ->setPrefix('action_details')
            ->setId("1--1");

        foreach($actionDetailModel->getDirectAttributeKeys() as $attributeKey){
            if(isset($actionDetailArr[$attributeKey])){
                $actionDetailModel->setData($attributeKey, $actionDetailArr[$attributeKey]);
            }
        }

        foreach($actionDetailModel->getSubActionDetailsKeys() as $subActionDetailsKey){
            if(isset($actionDetailArr[$subActionDetailsKey])){
                $actionDetailModel->loadSubActionArray($subActionDetailsKey, $actionDetailArr[$subActionDetailsKey]["action_details"][1], "action_details");
            }
        }

        return $actionDetailModel;
    }

    public function getLoadedActionDetailAsArray($actionDetailLoaded)
    {
        $out = [];

        $out['type'] = $actionDetailLoaded->getType();

        foreach($actionDetailLoaded->getDirectAttributeKeys() as $attributeKey){
            $out[$attributeKey] = $actionDetailLoaded->getData($attributeKey);
        }

        foreach($actionDetailLoaded->getSubActionDetailsKeys() as $subActionDetailsKey){
            $out[$subActionDetailsKey]['action_details'][1]['action_details'][1] = $actionDetailLoaded->asSubActionArray($subActionDetailsKey);
        }

        return $out;
    }


    public function getSimpleActionType($simpleAction){
        if(array_key_exists($simpleAction, $this->apSimpleActionsToTypes)){
            return $this->apSimpleActionsToTypes[$simpleAction];
        }
        throw new \Mexbs\AdditionalPromotions\SimpleActionNotFoundException(sprintf("Can't resolve type for simple action %s", $simpleAction));
    }

    public function getApRuleIdsOutOfRuleIds($ruleIds = []){
        $rulesCollection = $this->ruleCollectionFactory->create();
        $rulesCollection->addFieldToFilter("rule_id", ["in" => $ruleIds])
            ->addFieldToFilter("simple_action", array_keys($this->apSimpleActionsToTypes));

        $returnRuleIds = [];
        foreach($rulesCollection as $rule){
            $returnRuleIds[] = $rule->getId();
        }

        return $returnRuleIds;
    }

    public function getRulesCollectionById($ruleIds = []){
        $rulesCollection = $this->ruleCollectionFactory->create();
        return $rulesCollection->addFieldToFilter("rule_id", ["in" => $ruleIds]);
    }

    public function getApRuleMatchesForItem($item){
        $apRuleMatches = $item->getApRuleMatches();
        if(!is_array($apRuleMatches)
            && !empty($apRuleMatches)){
            $apRuleMatches = $this->serializer->unserialize($apRuleMatches);
            $item->setApRuleMatches($apRuleMatches);
        }
        return $apRuleMatches;
    }

    public function getApPriceTypeFlagsForItem($item){
        $apPriceTypeFlags = $item->getApPriceTypeFlags();
        if(!is_array($apPriceTypeFlags)
            && !empty($apPriceTypeFlags)){
            $apPriceTypeFlags = $this->serializer->unserialize($apPriceTypeFlags);
            $item->setApPriceTypeFlags($apPriceTypeFlags);
        }
        return $apPriceTypeFlags;
    }

    public function getIsOneOfTheItemsMarkedByRule( $ruleId,$items = []){
        if(is_array($items)){
        foreach($items as $item){
            $apRuleMatches = $this->getApRuleMatchesForItem($item);
            if(is_array($apRuleMatches)
                && !empty($apRuleMatches)
                && isset($apRuleMatches[$ruleId])){
                return true;
            }
        }
        }
        return false;
    }

    public function getIsDiscountBreakdownEnabled(){
        return $this->scopeConfig->getValue('additional_promotions/description_breakdown/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getIsDiscountBreakdownCollapsed(){
        return $this->scopeConfig->getValue('additional_promotions/description_breakdown/collapsed', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getConfigBreakdownType(){
        return $this->scopeConfig->getValue('additional_promotions/description_breakdown/type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getConfigIsSkipSpecialPrice(){
        return $this->scopeConfig->getValue('additional_promotions/skip_discount/skip_special_price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getConfigIsSkipCatalogRulePrice(){
        return $this->scopeConfig->getValue('additional_promotions/skip_discount/skip_catalog_rule_price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getConfigIsSkipTierPrice(){
        return $this->scopeConfig->getValue('additional_promotions/skip_discount/skip_tier_price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getConfigPromoBlockTitle(){
        return $this->scopeConfig->getValue('additional_promotions/promo_block_in_cart/promo_block_title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getItemPrice($item){
        $itemPrice = $this->validator->getItemPrice($item);
        return ($itemPrice*$item->getQty() - $item->getDiscountAmount())/$item->getQty();
    }

    public function getItemBasePrice($item){
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        return ($baseItemPrice*$item->getQty() - $item->getDiscountAmount())/$item->getQty();
    }

    public function getItemOriginalPrice($item){
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        return ($itemOriginalPrice*$item->getQty() - $item->getDiscountAmount())/$item->getQty();
    }

    public function getItemBaseOriginalPrice($item){
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);
        return ($baseItemOriginalPrice*$item->getQty() - $item->getDiscountAmount())/$item->getQty();
    }

    protected function _isRuleWithinUsageLimit($rule, $address){
        $ruleId = $rule->getId();
        if ($ruleId && $rule->getUsesPerCustomer()) {
            $customerId = $address->getQuote()->getCustomerId();
            /** @var \Magento\SalesRule\Model\Rule\Customer $ruleCustomer */
            $ruleCustomer = $this->customerFactory->create();
            $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
            if ($ruleCustomer->getId()) {
                if ($ruleCustomer->getTimesUsed() >= $rule->getUsesPerCustomer()) {
                    $rule->setIsValidForAddress($address, false);
                    return false;
                }
            }
        }
        return true;
    }

    protected function getImageDisplayPopupHtml($imageName){
        $imageUrl = $this->getSalesRulePopupImageUrl($imageName);
        return sprintf(
            '<img src="%s" />',
            $imageUrl
        );
    }

    public function getApRulesForQuote($quote){
        $apRules = [];
        $rules = $this->getRulesForQuote($quote);
        foreach($rules as $rule){
            if($this->isSimpleActionAp($rule->getSimpleAction())){
                $apRules[] = $rule;
            }
        }
        return $apRules;
    }

    public function getRulesToShowPromoBlockForQuote($quote){
        $apRules = [];
        $rules = $this->getRulesForQuote($quote);
        foreach($rules as $rule){
            if(!$rule->getDisplayPromoBlock()){
                continue;
            }
            if($rule->getHidePromoBlockIfRuleApplied()
                && $this->getIsOneOfTheItemsMarkedByRule($quote->getAllVisibleItems(), $rule->getId())){
                continue;
            }
            if($this->isSimpleActionSupportsPromoBlock($rule->getSimpleAction())){
                $apRules[] = $rule;
            }
        }
        return $apRules;
    }

    public function getRulesForQuote($quote){
        /**
         * @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection $rules
         */
        $rules = $this->ruleCollectionFactory->create();

        $address = $quote->getShippingAddress();

        $rules->setValidationFilter(
            $quote->getStore()->getWebsiteId(),
            $quote->getCustomerGroupId(),
            '',
            null,
            $address
        )->addFieldToFilter('is_active', 1)
            ->setOrder('sort_order', \Magento\Framework\Data\Collection\AbstractDb::SORT_ORDER_ASC)
            ->load();
        return $rules;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     */
    public function getPopupsHtmlToDisplayForQuote($quote){
        $popupsData = [];
        $address = $quote->getShippingAddress();
        /**
         * @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection $rules
         */
        $rules = $this->ruleCollectionFactory->create();
        $rules->setValidationFilter(
            $quote->getStore()->getWebsiteId(),
            $quote->getCustomerGroupId(),
            '',
            null,
            $address
        )->addFieldToFilter('is_active', 1)
        ->setOrder('sort_order', \Magento\Framework\Data\Collection\AbstractDb::SORT_ORDER_ASC)
        ->load();

        foreach($rules as $rule){
            if(!$this->_isRuleWithinUsageLimit($rule, $address)){
                continue;
            }

            if($rule->getDisplayPopupOnFirstVisit()
                && !empty($rule->getPopupOnFirstVisitImage())){
                $popupId = sprintf("%s_%s", $rule->getId(), 'first_visit');
                $popupsData[] = [
                    'id' => $popupId,
                    'html' => $this->getImageDisplayPopupHtml($rule->getPopupOnFirstVisitImage())
                ];
            }
        }

        return $popupsData;
    }

    protected function _getRulesToProcessOnItems($websiteId, $customerGroupId, $couponCode, $address){
        return $this->ruleCollectionFactory->create()
            ->setValidationFilter(
                $websiteId,
                $customerGroupId,
                $couponCode,
                null,
                $address
            )
            ->addFieldToFilter('is_active', 1)
            ->load();
    }

    protected function _sortItemsByPriority($items, $rules)
    {
        $itemsSorted = [];
        /** @var $rule \Magento\SalesRule\Model\Rule */
        foreach ($rules as $rule) {
            foreach ($items as $itemKey => $itemValue) {
                if ($rule->getActions()->validate($itemValue)) {
                    unset($items[$itemKey]);
                    array_push($itemsSorted, $itemValue);
                }
            }
        }

        if (!empty($itemsSorted)) {
            $items = array_merge($itemsSorted, $items);
        }

        return $items;
    }

    protected function _getItemsToProcessDiscountOn($items){
        $itemsToProcessDiscountOn = [];
        foreach($items as $item){
            if ($item->getNoDiscount()) {
                continue;
            }
            if ($item->getParentItem()) {
                continue;
            }
            $itemsToProcessDiscountOn[] = $item;
        }
        return $itemsToProcessDiscountOn;
    }

    protected function _getRulesSortedByPriorities($rules){
        $rulesPerPriorities = [];
        foreach($rules as $rule){
            $ruleSortOrder = $rule->getSortOrder();
            if(!isset($rulesPerPriorities[$ruleSortOrder])){
                $rulesPerPriorities[$ruleSortOrder] = [];
            }
            $rulesPerPriorities[$ruleSortOrder][] = $rule;
        }
        return $rulesPerPriorities;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return string
     */
    public function getQuoteItemId($item){
        if($item->getId()){
            return $item->getId();
        }
        $product = $item->getProduct();
        $optionBuyRequest = $item->getOptionByCode('info_buyRequest');
        $optionBuyRequestValue = (!$optionBuyRequest ? "-" : $optionBuyRequest->getValue());
        return hash("sha256", $product->getId()."_".$product->getName()."_".$optionBuyRequestValue);
    }

    protected function _getAvailableItemsToApplyRulesOn($items, $itemIdsToStopRulesProcessing){
        $availableItemsToApplyRulesOn = [];
        foreach($items as $item){
            $itemId = $this->getQuoteItemId($item);
            if(!in_array($itemId, $itemIdsToStopRulesProcessing)){
                $availableItemsToApplyRulesOn[$itemId] = $item;
            }
        }
        return $availableItemsToApplyRulesOn;
    }

    protected function _constructCartHintLineFromDirectChildren($directChildrenData, $operator){
        $itemsQtysToAdd = [];
        $itemsQtysToReduce = [];
        $itemsQtysToAddOrReduce = [];
        foreach($directChildrenData as $directChildData){
            if(($directChildData['to_add'] > 0
                || $directChildData['to_add'] === 'any'
                || $directChildData['to_add_more'])
                && ($directChildData['to_reduce'] > 0
                    || $directChildData['to_reduce'] === 'any'
                    || $directChildData['to_reduce_more'])
            ){
                $itemsQtysToAddOrReduce[] = [
                    'hint_singular' => $directChildData['hint_singular'],
                    'hint_plural' => $directChildData['hint_plural'],
                    'volume_type' => $directChildData['volume_type']
                ];
            }elseif($directChildData['to_add'] > 0
                || $directChildData['to_add_more']){
                $itemsQtysToAdd[] = [
                    'hint_singular' => $directChildData['hint_singular'],
                    'hint_plural' => $directChildData['hint_plural'],
                    'volume_type' => $directChildData['volume_type'],
                    'to_add' => $directChildData['to_add'],
                    'to_add_more' => $directChildData['to_add_more']
                ];
            }elseif($directChildData['to_reduce'] > 0
                || $directChildData['to_reduce_more']){
                $itemsQtysToReduce[] = [
                    'hint_singular' => $directChildData['hint_singular'],
                    'hint_plural' => $directChildData['hint_plural'],
                    'volume_type' => $directChildData['volume_type'],
                    'to_reduce' => $directChildData['to_reduce'],
                    'to_reduce_more' => $directChildData['to_reduce_more']
                ];
            }
        }

        $totalCount = count($itemsQtysToAddOrReduce) + count($itemsQtysToAdd) + count($itemsQtysToReduce);
        $cartHintLine = "";
        $currentIndex = 0;
        if(count($itemsQtysToAdd)){
            $cartHintLine .= "add ";
        }
        foreach($itemsQtysToAdd as $itemQtyToAdd){
            if($totalCount > 1 && $currentIndex > 0){
                if($currentIndex == $totalCount - 1){
                    $cartHintLine .= " ".$operator." ";
                }else{
                    $cartHintLine .= ", ";
                }
            }
            if($itemQtyToAdd['volume_type'] == "base_subtotal"){
                $cartHintLine .= sprintf(
                    "%s more products",
                    ($itemQtyToAdd['to_add'] == 0 ? "some" : $this->getCurrentCurrencySymbol().$itemQtyToAdd['to_add']." worth")
                );
            }elseif($itemQtyToAdd['volume_type'] == "total_qty"){
                $cartHintLine .= sprintf(
                    "%s more products",
                    ($itemQtyToAdd['to_add'] == 0 ? "some" : $itemQtyToAdd['to_add'])
                );
            }elseif($itemQtyToAdd['volume_type'] == "weight"){
                $cartHintLine .= sprintf(
                    "%s more products",
                    ($itemQtyToAdd['to_add'] == 0 ? "some" : $itemQtyToAdd['to_add'].$this->directoryHelper->getWeightUnit()),
                    $this->directoryHelper->getWeightUnit()
                );
            }else{
                $cartHintLine .= sprintf(
                    "%s%s%s more %s",
                    ($itemQtyToAdd['volume_type'] == 'amount' ? $this->getCurrentCurrencySymbol() : ""),
                    ($itemQtyToAdd['to_add'] == 0 ? "some " : ($itemQtyToAdd['to_add'] == 1 ? " one " : " ".$itemQtyToAdd['to_add'])),
                    ($itemQtyToAdd['volume_type'] == 'amount' ? " worth" : ""),
                    ($itemQtyToAdd['to_add'] == 1 ? $itemQtyToAdd['hint_singular'] : $itemQtyToAdd['hint_plural'])
                );
            }

            $currentIndex++;
        }
        if(count($itemsQtysToReduce)){
            if($cartHintLine != ""){
                $cartHintLine .= ", ";
            }
            $cartHintLine .= "remove ";
        }
        foreach($itemsQtysToReduce as $itemQtyToReduce){
            if($totalCount > 1 && $currentIndex > 0){
                if($currentIndex == $totalCount - 1){
                    $cartHintLine .= " ".$operator." ";
                }else{
                    $cartHintLine .= ", ";
                }
            }
            if($itemQtyToReduce['volume_type'] == "base_subtotal"){
                $cartHintLine .= sprintf(
                    "%s%s worth products",
                    $this->getCurrentCurrencySymbol(),
                    $itemQtyToReduce['to_reduce']
                );
            }elseif($itemQtyToReduce['volume_type'] == "total_qty"){
                $cartHintLine .= sprintf(
                    "%s products",
                    $itemQtyToReduce['to_reduce']
                );
            }elseif($itemQtyToReduce['volume_type'] == "weight"){
                $cartHintLine .= sprintf(
                    "%s %s of products",
                    $itemQtyToReduce['to_reduce'],
                    $this->directoryHelper->getWeightUnit()
                );
            }else{
                $cartHintLine .= sprintf(
                    "%s more %s",
                    ($itemQtyToReduce['to_reduce'] == 0 ? "some " : ($itemQtyToReduce['to_reduce'] == 1 ? "one " : $itemQtyToReduce['to_reduce'])),
                    ($itemQtyToReduce['to_reduce'] == 1 ? $itemQtyToReduce['hint_singular'] : $itemQtyToReduce['hint_plural'])
                );
            }

            $currentIndex++;
        }
        if(count($itemsQtysToAddOrReduce)){
            if($cartHintLine != ""){
                $cartHintLine .= ", ";
            }
            $cartHintLine .= "add or remove ";
        }
        foreach($itemsQtysToAddOrReduce as $itemQtyToAddOrReduce){
            if($totalCount > 1 && $currentIndex > 0){
                if($currentIndex == $totalCount - 1){
                    $cartHintLine .= " ".$operator." ";
                }else{
                    $cartHintLine .= ", ";
                }
            }
            if(in_array($itemQtyToAddOrReduce['volume_type'], ["base_subtotal","total_qty","weight"])){
                $cartHintLine .= "some products";
            }else{
                $cartHintLine .= sprintf(
                    "some %s",
                    $itemQtyToAddOrReduce['hint_plural']
                );
            }
            $currentIndex++;
        }
        return $cartHintLine;
    }

    protected function _isNotEmptyRuleChildData($childData){
        return($childData['to_add'] > 0
            || $childData['to_add'] === 'any'
            || $childData['to_add_more']
            || $childData['to_reduce'] > 0
            || $childData['to_reduce'] === 'any'
            || $childData['to_reduce_more']);
    }

    protected function _collapseOnlyChildrenBranches($cartHintData){
        $cartHintDataCollapsed = [];

        $cartHintDataKeys = array_keys($cartHintData);
        if(in_array('to_add', $cartHintDataKeys, true)){
            return $cartHintData;
        }

        if(count($cartHintData[$cartHintDataKeys[0]]) == 1
            && !isset($cartHintData[$cartHintDataKeys[0]][0]['to_add'])){
            return $this->_collapseOnlyChildrenBranches($cartHintData[$cartHintDataKeys[0]][0]);
        }else{
            $cartHintDataCollapsed[$cartHintDataKeys[0]] = [];
            foreach($cartHintData[$cartHintDataKeys[0]] as $subCartHintData){
                $cartHintDataCollapsed[$cartHintDataKeys[0]][] = $this->_collapseOnlyChildrenBranches($subCartHintData);
            }
        }

        return $cartHintDataCollapsed;
    }

    protected function _getCartHintDataWithoutRedundantChildren($cartHintData, $rootOperator){
        $cartDataWithoutRedundantChildren = [];
        $logicalOperatorKeys = ['and', 'or'];

        if(in_array($rootOperator, $logicalOperatorKeys, true)){
            $currentIndex = 0;
            foreach($cartHintData as $cartHintDataValue){
                $cartHintDataValueArrayKeys = array_keys($cartHintDataValue);
                if(isset($cartHintDataValueArrayKeys[0])){
                    if(in_array($cartHintDataValueArrayKeys[0], $logicalOperatorKeys, true)){;
                        $subCartDataWithoutRedundantChildren = $this->_getCartHintDataWithoutRedundantChildren($cartHintDataValue[$cartHintDataValueArrayKeys[0]], $cartHintDataValueArrayKeys[0]);
                        if(!empty($subCartDataWithoutRedundantChildren)){
                            $cartDataWithoutRedundantChildren[][$cartHintDataValueArrayKeys[0]] = $subCartDataWithoutRedundantChildren;
                        }
                    }else{
                        if($this->_isNotEmptyRuleChildData($cartHintDataValue)){
                            $cartDataWithoutRedundantChildren[] = $cartHintDataValue;
                        }
                    }
                }

                $currentIndex++;
            }
        }
        return $cartDataWithoutRedundantChildren;
    }

    protected function _constructCartHint($cartHintData, $rootOperator){
        $cartHintDataWithoutRedundantChildren = $this->_getCartHintDataWithoutRedundantChildren($cartHintData, $rootOperator);
        $cartHintDataWithoutRedundantChildrenCollapsed = $this->_collapseOnlyChildrenBranches([
            $rootOperator => $cartHintDataWithoutRedundantChildren
        ]);
        $cartHintDataWithoutRedundantChildrenCollapsedKeys = array_keys($cartHintDataWithoutRedundantChildrenCollapsed);
        $rootOperator = $cartHintDataWithoutRedundantChildrenCollapsedKeys[0];
        return $this->_constructCartHintLine($cartHintDataWithoutRedundantChildrenCollapsed[$rootOperator], $rootOperator, true);
    }

    protected function _constructCartHintLine($cartHintData, $rootOperator, $isEmptyHintLinePrefix){
        $cartHintLine = "";
        $logicalOperatorKeys = ['and', 'or'];

        if(in_array($rootOperator, $logicalOperatorKeys, true)){
            $currentIndex = 0;
            $directChildrenData = [];
            foreach($cartHintData as $cartHintDataValue){
                $cartHintDataValueArrayKeys = array_keys($cartHintDataValue);
                if(isset($cartHintDataValueArrayKeys[0])){
                    if(in_array($cartHintDataValueArrayKeys[0], $logicalOperatorKeys, true)){
                        if(count($cartHintData) > 1){
                            if($currentIndex == (count($cartHintData) - 1)){
                                $cartHintLine .= $rootOperator." ";
                            }elseif($currentIndex > 0){
                                $cartHintLine .= ", ";
                            }
                        }
                        $cartHintLine .= $this->_constructCartHintLine($cartHintDataValue[$cartHintDataValueArrayKeys[0]], $cartHintDataValueArrayKeys[0], ($cartHintLine == "" && $isEmptyHintLinePrefix));
                    }else{
                        if($this->_isNotEmptyRuleChildData($cartHintDataValue)){
                            $directChildrenData[] = $cartHintDataValue;
                        }
                    }
                }

                $currentIndex++;
            }
            if(count($directChildrenData)){
                if(!$isEmptyHintLinePrefix || $cartHintLine != ""){
                    if(count($directChildrenData) == 1){
                        $cartHintLine .= " ".$rootOperator." ";
                    }else{
                        $cartHintLine .= ", ";
                    }
                }
                $cartHintLine .= $this->_constructCartHintLineFromDirectChildren($directChildrenData, $rootOperator);
            }
        }
        return $cartHintLine;
    }

    public function getCartHintAddData($operator, $desiredVolume, $currentVolume){
        //'==', '!=', '>=', '>', '<=', '<', '()', '!()'
        $cartHintAddData = [];
        $desiredAndCurrentDifference = $desiredVolume - $currentVolume;
        $operatorsData = [
            '>=' => [
                'to_add' => max($desiredAndCurrentDifference, 0),
                'to_add_more' => false,
                'to_reduce' => 0,
                'to_reduce_more' => false,
                'opposite_operator' => '<'
            ],
            '>' => [
                'to_add' => max($desiredAndCurrentDifference, 0),
                'to_add_more' => ($desiredAndCurrentDifference >= 0),
                'to_reduce' => 0,
                'to_reduce_more' => false,
                'opposite_operator' => '<='
            ],
            '<=' => [
                'to_add' => 0,
                'to_add_more' => false,
                'to_reduce' => max(-$desiredAndCurrentDifference, 0),
                'to_reduce_more' => false,
                'opposite_operator' => '>'
            ],
            '<' => [
                'to_add' => 0,
                'to_add_more' => false,
                'to_reduce' => max(-$desiredAndCurrentDifference, 0),
                'to_reduce_more' => ($desiredAndCurrentDifference <= 0),
                'opposite_operator' => '>='
            ],
            '==' => [
                'to_add' => max($desiredAndCurrentDifference, 0),
                'to_add_more' => false,
                'to_reduce' => max(-$desiredAndCurrentDifference, 0),
                'to_reduce_more' => false,
                'opposite_operator' => '!='
            ],
            '!=' => [
                'to_add' => ($desiredAndCurrentDifference == 0 ? 'any' : 0),
                'to_add_more' => false,
                'to_reduce' => ($desiredAndCurrentDifference == 0 ? 'any' : 0),
                'to_reduce_more' => false,
                'opposite_operator' => '=='
            ],
        ];

        if(in_array($operator, array_keys($operatorsData), true)){
            $cartHintAddData['to_add'] = $operatorsData[$operator]['to_add'];
            $cartHintAddData['to_add_more'] = $operatorsData[$operator]['to_add_more'];
            $cartHintAddData['to_reduce'] = $operatorsData[$operator]['to_reduce'];
            $cartHintAddData['to_reduce_more'] = $operatorsData[$operator]['to_reduce_more'];
            $cartHintAddData['to_add_inverted'] = $operatorsData[$operatorsData[$operator]['opposite_operator']]['to_add'];
            $cartHintAddData['to_add_more_inverted'] = $operatorsData[$operatorsData[$operator]['opposite_operator']]['to_add_more'];
            $cartHintAddData['to_reduce_inverted'] = $operatorsData[$operatorsData[$operator]['opposite_operator']]['to_reduce'];
            $cartHintAddData['to_reduce_more_inverted'] = $operatorsData[$operatorsData[$operator]['opposite_operator']]['to_reduce_more'];
        }
        return $cartHintAddData;
    }

    protected function _checkCanProcessRuleAndGetHintsData($rule, $address)
    {
        $returnData = [
            'can_process_rule' => false,
            'cart_hint' => null
        ];
        if ($rule->hasIsValidForAddress($address) && !$address->isObjectNew()) {
            return $rule->getIsValidForAddress($address);
        }

        /**
         * check per coupon usage limit
         */
        if ($rule->getCouponType() != \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON) {
            $couponCode = $address->getQuote()->getCouponCode();
            if (strlen($couponCode)) {
                /** @var \Magento\SalesRule\Model\Coupon $coupon */
                $coupon = $this->couponFactory->create();
                $coupon->load($couponCode, 'code');
                if ($coupon->getId()) {
                    // check entire usage limit
                    if ($coupon->getUsageLimit() && $coupon->getTimesUsed() >= $coupon->getUsageLimit()) {
                        $rule->setIsValidForAddress($address, false);
                        return $returnData;
                    }
                    // check per customer usage limit
                    $customerId = $address->getQuote()->getCustomerId();
                    if ($customerId && $coupon->getUsagePerCustomer()) {
                        $couponUsage = $this->objectFactory->create();
                        $this->usageFactory->create()->loadByCustomerCoupon(
                            $couponUsage,
                            $customerId,
                            $coupon->getId()
                        );
                        if ($couponUsage->getCouponId() &&
                            $couponUsage->getTimesUsed() >= $coupon->getUsagePerCustomer()
                        ) {
                            $rule->setIsValidForAddress($address, false);
                            return $returnData;
                        }
                    }
                }
            }
        }

        /**
         * check per rule usage limit
         */
        $ruleId = $rule->getId();
        if ($ruleId && $rule->getUsesPerCustomer()) {
            $customerId = $address->getQuote()->getCustomerId();
            /** @var \Magento\SalesRule\Model\Rule\Customer $ruleCustomer */
            $ruleCustomer = $this->customerFactory->create();
            $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
            if ($ruleCustomer->getId()) {
                if ($ruleCustomer->getTimesUsed() >= $rule->getUsesPerCustomer()) {
                    $rule->setIsValidForAddress($address, false);
                    return $returnData;
                }
            }
        }
        $rule->afterLoad();
        /**
         * quote does not meet rule's conditions
         */
        $isValidForAddress = $rule->validate($address);
        $ruleValidationCartHintData = $rule->getConditions()->getLastCartHintData();
        if($ruleValidationCartHintData
            && !$isValidForAddress
            && ($rule->getCouponType() == \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON)){
            $logicalOperatorKeys = ['and', 'or'];
            $cartHintDataArrayKeys = array_keys($ruleValidationCartHintData);
            if(isset($cartHintDataArrayKeys[0]) && in_array($cartHintDataArrayKeys[0], $logicalOperatorKeys, true)){
                $returnData['cart_hint'] = ucfirst($this->_constructCartHint($ruleValidationCartHintData[$cartHintDataArrayKeys[0]], $cartHintDataArrayKeys[0]));
            }
            $returnData['can_process_rule'] = $isValidForAddress;
        }

        if(!$isValidForAddress){
            $rule->setIsValidForAddress($address, false);
            return $returnData;
        }

        /**
         * passed all validations, remember to be valid
         */
        $rule->setIsValidForAddress($address, true);

        $returnData['can_process_rule'] = true;
        return $returnData;
    }

    protected function _getItemsWithoutFixedPriceRuleApplied($items, $processedItemsByFixedPriceApRules){
        $itemsWithoutFixedPriceRuleApplied = [];
        foreach($items as $item){
            $itemId = $this->getQuoteItemId($item);
            if(!array_key_exists($itemId, $processedItemsByFixedPriceApRules)){
                $itemsWithoutFixedPriceRuleApplied[] = $item;
            }
        }
        return $itemsWithoutFixedPriceRuleApplied;
    }

    protected function _setDiscountData($discountData, $item)
    {
        $item->setDiscountAmount($discountData->getAmount());
        $item->setBaseDiscountAmount($discountData->getBaseAmount());
        $item->setOriginalDiscountAmount($discountData->getOriginalAmount());
        $item->setBaseOriginalDiscountAmount($discountData->getBaseOriginalAmount());

        return $this;
    }

    protected function _eventFix(
        \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        \Magento\SalesRule\Model\Rule $rule,
        $qty
    ) {
        $quote = $item->getQuote();
        $address = $item->getAddress();

        $this->eventManager->dispatch(
            'salesrule_validator_process',
            [
                'rule' => $rule,
                'item' => $item,
                'address' => $address,
                'quote' => $quote,
                'qty' => $qty,
                'result' => $discountData
            ]
        );

        return $this;
    }

    protected function _getDiscountData($item, $rule)
    {
        $qty = $this->validatorUtility->getItemQty($item, $rule);

        $discountCalculator = $this->calculatorFactory->create($rule->getSimpleAction());
        $qty = $discountCalculator->fixQuantity($qty, $rule);
        $discountData = $discountCalculator->calculate($rule, $item, $qty);

        $this->_eventFix($discountData, $item, $rule, $qty);
        $this->validatorUtility->deltaRoundingFix($discountData, $item);

        /**
         * We can't use row total here because row total not include tax
         * Discount can be applied on price included tax
         */

        $this->validatorUtility->minFix($discountData, $item, $qty);

        return $discountData;
    }

    protected function _processRuleOnItem($rule, $currentItem, $availableItemsToApplyRulesOn, $address, $processedApRules, $processedItemsByFixedPriceApRules, $itemIdsToStopRulesProcessing){
        $returnData = [];

        $returnData['is_ap_rule_matched'] = false;

        $simpleAction = $rule->getSimpleAction();
        $isRuleAp = $this->isSimpleActionAp($simpleAction);

        if($isRuleAp){
            if(!in_array($rule->getId(), $processedApRules)){
                $actionDetailModel = $this->getLoadedActionDetail($rule);
                if($actionDetailModel){
                    $itemsToProcessTheRuleOn = $availableItemsToApplyRulesOn;
                    $isRuleApAndFixedPrice = $this->isRuleApAndFixedPrice($rule);
                    if($isRuleApAndFixedPrice){
                        $itemsToProcessTheRuleOn = $this->_getItemsWithoutFixedPriceRuleApplied($itemsToProcessTheRuleOn, $processedItemsByFixedPriceApRules);
                    }

                    $matchResult = $actionDetailModel->markMatchingItemsAndGetHint($itemsToProcessTheRuleOn, $address);

                    foreach($itemsToProcessTheRuleOn as $item){
                        $itemApRuleMatches = $this->getApRuleMatchesForItem($item);
                        $itemApRuleMatches = (is_array($itemApRuleMatches) ? $itemApRuleMatches : []);
                        if(isset($itemApRuleMatches[$rule->getId()])){
                            $discountData = $this->_getDiscountData($item, $rule);
                            $this->_setDiscountData($discountData, $item);

                            $itemId = $this->getQuoteItemId($item);

                            if($isRuleApAndFixedPrice){
                                $processedItemsByFixedPriceApRules[$itemId] = $rule->getId();
                            }

                            if($rule->getStopRulesProcessing()){
                                $itemIdsToStopRulesProcessing[] = $itemId;
                            }

                            $returnData['is_ap_rule_matched'] = true;
                        }
                    }
                }

                $processedApRules[] = $rule->getId();
            }

            $returnData['processed_ap_rules'] = $processedApRules;
            $returnData['processed_items_by_fixed_price_ap_rules'] = $processedItemsByFixedPriceApRules;
            if(isset($matchResult['cart_hint'])){
                $returnData['cart_hint'] = $matchResult['cart_hint'];
            }
            if(isset($matchResult['coupon_not_valid_cart_hint'])){
                $returnData['coupon_not_valid_cart_hint'] = $matchResult['coupon_not_valid_cart_hint'];
            }
        }else{
            if (!$rule->getActions()->validate($currentItem)) {
                $childItems = $currentItem->getChildren();
                $isContinue = true;
                if (!empty($childItems)) {
                    foreach ($childItems as $childItem) {
                        if ($rule->getActions()->validate($childItem)) {
                            $isContinue = false;
                        }
                    }
                }
                if ($isContinue) {
                    return $returnData;
                }
            }

            $discountData = $this->_getDiscountData($currentItem, $rule);
            $this->_setDiscountData($discountData, $currentItem);

            if($rule->getStopRulesProcessing()){
                $currentItemId = $this->getQuoteItemId($currentItem);
                $itemIdsToStopRulesProcessing[] = $currentItemId;
            }
        }

        $returnData['stop_rules_processing_item_ids'] = $itemIdsToStopRulesProcessing;

        return $returnData;
    }

    protected function _addCartHintsToQuote($cartHintsPerRuleId, $quote){
        $hintMessages = ($quote->getHintMessages() ? $quote->getHintMessages() : []);
        foreach($cartHintsPerRuleId as $ruleId => $cartHintMessage){
            $hintMessages[$ruleId] = $cartHintMessage;
        }
        $quote->setHintMessages($hintMessages);
    }

    protected function cleanUpCachedDataCreatedByCoreCalls($address){
        $address->unsCartFixedRules();
    }
    public function processRulesOnItems($items, $address, $websiteId, $customerGroupId, $couponCode){
        $rulesCollection = $this->_getRulesToProcessOnItems($websiteId, $customerGroupId, $couponCode, $address);

        $items = $this->_sortItemsByPriority($items, $rulesCollection);
        $items = $this->_getItemsToProcessDiscountOn($items);

        if(!count($rulesCollection)
            || !count($items)){
            return;
        }

        $rules = [];
        foreach($rulesCollection as $rule){
            $rules[] = $rule;
        }

        $itemIdsToStopRulesProcessing = [];
        $processedApRules = [];
        $processedItemsByFixedPriceApRules = [];
        $cartHintsPerRuleId = [];
        $cartCouponNotValidHintsPerRuleId = [];

        $validatedRules = [];
        foreach($rules as $rule){
            $canProcessRuleAndHintData = $this->_checkCanProcessRuleAndGetHintsData($rule, $address);

            if (!$canProcessRuleAndHintData['can_process_rule']) {
                if(!empty($canProcessRuleAndHintData['cart_hint'])){
                    $ruleActionsHintLabel = $rule->getActionsHintLabel();
                    if($ruleActionsHintLabel){
                        $cartHintsPerRuleId[$rule->getId()] = $canProcessRuleAndHintData['cart_hint'] .', '.$ruleActionsHintLabel;
                    }
                }
            }else{
                $validatedRules[] = $rule;
            }
        }

        $validatedRulesPerPriorities = $this->_getRulesSortedByPriorities($validatedRules);
        ksort($validatedRulesPerPriorities);


        foreach($validatedRulesPerPriorities as $priority => $rules){
            foreach($items as $item){
                $itemId = $this->getQuoteItemId($item);
                if(!in_array($itemId, $itemIdsToStopRulesProcessing)){
                    $avalilableItemsToApplyRulesOn = $this->_getAvailableItemsToApplyRulesOn($items, $itemIdsToStopRulesProcessing);

                    foreach($rules as $rule){
                        $processResult = $this->_processRuleOnItem($rule, $item, $avalilableItemsToApplyRulesOn, $address, $processedApRules, $processedItemsByFixedPriceApRules, $itemIdsToStopRulesProcessing);
                        if(isset($processResult['stop_rules_processing_item_ids'])){
                            $itemIdsToStopRulesProcessing = $processResult['stop_rules_processing_item_ids'];
                        }

                        if(isset($processResult['processed_items_by_fixed_price_ap_rules'])){
                            $processedItemsByFixedPriceApRules = $processResult['processed_items_by_fixed_price_ap_rules'];
                        }

                        if(isset($processResult['processed_ap_rules'])){
                            $processedApRules = $processResult['processed_ap_rules'];
                        }


                        if(isset($processResult['cart_hint'])
                            &&
                            ((isset($processResult['is_ap_rule_matched'])
                                    && ($processResult['is_ap_rule_matched'] === true))
                                || ($rule->getCouponType() == \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON)
                            )
                        ){
                            $cartHintsPerRuleId[$rule->getId()] = $processResult['cart_hint'];
                        }

                        if(isset($processResult['coupon_not_valid_cart_hint'])
                            &&
                            (((!isset($processResult['is_ap_rule_matched'])
                                    || !$processResult['is_ap_rule_matched']))
                                && ($rule->getCouponType() != \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON)
                            )
                        ){
                            $cartCouponNotValidHintsPerRuleId[$rule->getId()] = $processResult['coupon_not_valid_cart_hint'];
                        }
                    }
                }
            }
        }

        $this->cleanUpCachedDataCreatedByCoreCalls($address);

        $this->_addCartHintsToQuote($cartHintsPerRuleId, $address->getQuote());
        $this->_addCartHintsToQuote($cartCouponNotValidHintsPerRuleId, $address->getQuote());
    }


    public function getCurrentCurrencySymbol(){
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->currencyFactory->create()->load($currencyCode);
        return $currency->getCurrencySymbol();
    }


    public function getFullProductImageUrl($productImageUrl){
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $productImageUrl;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    public function getPromoBlockRulesHtmlArray($quote){
        $activeApRulesThatShowPromoBlock = $this->getRulesToShowPromoBlockForQuote($quote);

        $address = $quote->getShippingAddress();

        $rulesHtml = [];
        foreach($activeApRulesThatShowPromoBlock as $rule){
            if (!$this->validatorUtility->canProcessRule($rule, $address)) {
                continue;
            }

            $ruleActionDetailModel = $this->getLoadedActionDetail($rule);
            /**
             * @var \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine $ruleActionDetailModel
             */
            $cartBlockType = $ruleActionDetailModel->getCartBlockType();
            /**
             * @var \Magento\Framework\View\Element\Template $cartBlock
             */
            $cartBlock = $this->objectManager->create($cartBlockType,
                [
                    'rule' => $rule,
                    'quote' => $quote
                ]
            );

            $rulesHtml[] = $cartBlock->toHtml();
        }

        return $rulesHtml;
    }

    public function getProductAttributeValue($productId, $attributeCode){
        /**
         * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productsCollection
         */
        $productsCollection = $this->productCollectionFactory->create();
        $productsCollection->addAttributeToSelect($attributeCode)
            ->addIdFilter($productId);
        $product = $productsCollection->getFirstItem();
        if(!$product || !$product->getId()){
            return null;
        }
        return $product->getData($attributeCode);
    }

    public function getRuleGroupPromoBlockProductIds($rule, $groupNumber){
        $select = $this->connection->select()
            ->from($this->connection->getTableName('apactionrule_product'), ['product_id'])
            ->where(sprintf("rule_id='%s' AND group_number='%s'", $rule->getId(), $groupNumber));
        return $this->connection->fetchCol($select);
    }
}