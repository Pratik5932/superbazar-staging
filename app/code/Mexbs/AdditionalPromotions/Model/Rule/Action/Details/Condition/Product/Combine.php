<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product;

class Combine extends \Magento\SalesRule\Model\Rule\Condition\Product\Combine
{
    const DISCOUNT_TYPE_PERCENT = 'percent';
    const DISCOUNT_TYPE_FIXED = 'fixed';
    const DISCOUNT_TYPE_FIXED_PRICE = 'fixed_price';

    protected $discountPriceTypeOptions;
    protected $aggregatorValueOptions;
    protected $apHelper;
    protected $logger;
    private $ruleAvailProdsIndexCollFactory;
    private $productCollectionFactory;
    private $apRuleProductResourceFactory;
    private $productFactory;
    private $catalogProductVisibility;
    protected $type = 'Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine';

    const DISCOUNT_PRICE_TYPE_CHEAPEST = 'cheapest';
    const DISCOUNT_PRICE_TYPE_MOST_EXPENSIVE = 'most_expensive';

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct,
        \Mexbs\AdditionalPromotions\Helper\Data $apHelper,
        \Mexbs\AdditionalPromotions\Logger\Logger $logger,
        \Mexbs\AdditionalPromotions\Model\ResourceModel\RuleAvailableProductsIndex\CollectionFactory $ruleAvailProdsIndexCollFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Mexbs\AdditionalPromotions\Model\ResourceModel\RuleAvailableProductsIndexFactory $apRuleProductResourceFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        array $data = []
    ) {
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->apHelper = $apHelper;
        $this->logger = $logger;
        $this->ruleAvailProdsIndexCollFactory = $ruleAvailProdsIndexCollFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->apRuleProductResourceFactory = $apRuleProductResourceFactory;
        $this->productFactory = $productFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->setType($this->type)
            ->setActionDetails([]);

        $this->discountPriceTypeOptions = [
            self::DISCOUNT_PRICE_TYPE_CHEAPEST => __('cheapest'),
            self::DISCOUNT_PRICE_TYPE_MOST_EXPENSIVE => __('most expensive')
        ];
        $this->aggregatorValueOptions = [
            1 => __('TRUE'),
            0 => __('FALSE')
        ];
        $this->discountTypes = [
            self::DISCOUNT_TYPE_PERCENT,
            self::DISCOUNT_TYPE_FIXED,
            self::DISCOUNT_TYPE_FIXED_PRICE
        ];
    }

    public function collectValidatedAttributesForGroup($productCollection, $groupNumber){
        $this->getGroupActionDetail($groupNumber)->collectValidatedAttributes($productCollection);
    }

    public function validateProductForGroupWithoutQuote($groupNumber, $product){
        return $this->getGroupActionDetail($groupNumber)->validateProductWithoutQuote($product);
    }

    public function validateProductWithoutQuote($product){
        if (!$this->getConditions()) {
            return true;
        }

        $all = $this->getAggregator() === 'all';
        $true = (bool)$this->getValue();

        foreach ($this->getConditions() as $cond) {
            $validated = $cond->validateProductWithoutQuote($product);
            if ($all && $validated !== $true) {
                return false;
            } elseif (!$all && $validated === $true) {
                return true;
            }
        }
        return $all ? true : false;
    }

    public function hasAddressConditionsInActionInAnyGroup(){
        foreach($this->getNonEmptyGroupNumbers() as $groupNumber){
            if($this->getGroupActionDetail($groupNumber)->hasAddressConditionsInAction()){
                return true;
            }
        }
        return false;
    }

    public function hasAddressConditionsInAction(){
        if (!$this->getConditions()) {
            return false;
        }

        foreach ($this->getConditions() as $cond) {
            if(($cond instanceof \Magento\SalesRule\Model\Rule\Condition\Address)
                || $cond->hasAddressConditionsInAction()){
                return true;
            }
        }
        return false;
    }

    protected function getGroupProductIds($groupNumber){

    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     */
    public function getAlreadyAddedMatchingRuleItemsWithDisplayData($quote){
        $matchingItemsDataPerGroups = [];
        $remainingQtyPerGroup = [];
        $nonEmptyGroupNumbers = $this->getNonEmptyGroupNumbers();
        foreach($quote->getAllVisibleItems() as $item){
            $availableMatchingQtyForItem = $this->getMaximumAvailableQtyForItem($item);
            if($availableMatchingQtyForItem == 0){
                continue;
            }
            foreach($nonEmptyGroupNumbers as $groupNumber){
                $groupProductIds = $this->apHelper->getRuleGroupPromoBlockProductIds($this->getRule(), $groupNumber);
                if(in_array($item->getProduct()->getId(), $groupProductIds)){
                    if(!isset($matchingItemsDataPerGroups[$groupNumber])){
                        $matchingItemsDataPerGroups[$groupNumber] = [];
                    }
                    if(!isset($remainingQtyPerGroup[$groupNumber])){
                        $remainingQtyPerGroup[$groupNumber] = $this->getGroupQty($groupNumber);
                    }
                    if($remainingQtyPerGroup[$groupNumber] == 0){
                        continue;
                    }

                    $matchedQty = min($remainingQtyPerGroup[$groupNumber], $availableMatchingQtyForItem);

                    $productImage = $item->getProduct()->getData('image');
                    if(!$productImage){
                        $productImage = $this->productFactory->create()
                            ->load($item->getProduct()->getId(), 'image')
                            ->getData('image');
                    }
                    $matchingItemsDataPerGroups[$groupNumber][] = [
                        'item' => $item,
                        'title' => $item->getProduct()->getName(),
                        'image' => $this->apHelper->getFullProductImageUrl($productImage),
                        'qty' => $matchedQty
                    ];
                    $remainingQtyPerGroup[$groupNumber] -= $matchedQty;

                    continue 2;
                }
            }
        }
        return $matchingItemsDataPerGroups;
    }

    public function getIsSomeProductsHasOptions(){
        /**
         * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
         */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->getSelect()
            ->join(
                ['a' => 'apactionrule_product'],
                "e.entity_id = a.product_id"
            )->where(
                sprintf(
                    '(rule_id = "%s") AND (e.type_id = "%s" OR product_has_custom_options = 1)',
                    $this->getRule()->getId(),
                    \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
                )
            )->limit(1);
        if($productCollection->getSize() == 0){
            return false;
        }
        return true;
    }

    public function getIsRuleHasSelections(){
        foreach($this->getNonEmptyGroupNumbers() as $groupNumber){
            if($this->apRuleProductResourceFactory->create()->getNumberOfProductsPerRuleAndGroup($this->getRule()->getId(), $groupNumber) > 1){
                return true;
            }
        }
        return false;
    }

    public function getPromoProductGroupsWithDisplayData(){
        /**
         * @var \Mexbs\AdditionalPromotions\Model\ResourceModel\RuleAvailableProductsIndex\Collection $ruleAvailProdsIndexGroupsColl
         */
        $ruleAvailProdsIndexGroupsColl = $this->ruleAvailProdsIndexCollFactory->create();
        $ruleAvailProdsIndexGroupsColl->addFieldToFilter('rule_id', $this->getRule()->getId())
            ->getSelect()->group(["rule_id", "group_number"]);

        if(count($this->getNonEmptyGroupNumbers()) > count($ruleAvailProdsIndexGroupsColl)){
            return [];
        }

        $promoProductGroups = [];
        foreach($ruleAvailProdsIndexGroupsColl as $ruleAvailProdGroup){
            $groupNumber = $ruleAvailProdGroup->getGroupNumber();

            /**
             * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
             */
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->getSelect()
                ->join(
                    ['a' => 'apactionrule_product'],
                    "e.entity_id = a.product_id"
                )->where(
                    sprintf(
                        '(rule_id = "%s") AND (group_number = "%s")',
                        $this->getRule()->getId(),
                        $groupNumber
                    )
                );
            $productCollection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());


            if($productCollection->getSize() == 0){
                return [];
            }

            if(!isset($promoProductGroups[$groupNumber])){
                $promoProductGroups[$groupNumber] = [];
            }

            $promoProductGroups[$groupNumber]['title_singular'] = $this->getGroupTitleSingular($groupNumber);
            $promoProductGroups[$groupNumber]['title_plural'] = $this->getGroupTitlePlural($groupNumber);
            $promoProductGroups[$groupNumber]['qty'] = $this->getGroupQty($groupNumber);

            $productCollection->addAttributeToSelect('image')
                ->addAttributeToSelect('name');

            $shortProductImageUrl = $productCollection
                ->getFirstItem()
                ->getImage();
            $promoProductGroups[$groupNumber]['image'] = $this->apHelper->getFullProductImageUrl($shortProductImageUrl);
            $promoProductGroups[$groupNumber]['first_product_id'] = $productCollection->getFirstItem()->getId();
            $productCollection->clear()->addOptionsToResult();
            $promoProductGroups[$groupNumber]['products'] = $productCollection;
        }
        return $promoProductGroups;
    }


    protected function _getItemToCheckTierOrSpecialPrice($item){
        $itemProductToCheckTierOrSpecialPrice = $item->getProduct();
        if($item->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
            $itemChildren = $item->getChildren();
            if(count($itemChildren) > 0
                && isset($itemChildren[0])){
                $itemToCheckTierOrSpecialPrice = $itemChildren[0];
                if($itemToCheckTierOrSpecialPrice->getProduct()){
                    $itemProductToCheckTierOrSpecialPrice = $itemToCheckTierOrSpecialPrice->getProduct();
                }
            }
        }
        return $itemProductToCheckTierOrSpecialPrice;
    }

    protected function _getPriceTypeFlags($item, $itemProduct){
        if(!$this->apHelper->getApPriceTypeFlagsForItem($item)){
            $this->_setPriceTypeFlags($item, $itemProduct);
        }
        return $this->apHelper->getApPriceTypeFlagsForItem($item);
    }

    protected function _setPriceTypeFlags($item, $itemProduct){
        $itemOriginalPrice = $item->getOriginalPrice();

        $tierPrice = $itemProduct->getTierPrice($item->getQty());
        if(!$tierPrice){
            $tierPrice = $itemOriginalPrice;
        }
        $specialPrice = $this->apHelper->getSpecialPrice($itemProduct, $itemOriginalPrice);
        $catalogRulePrice = $this->apHelper->getCatalogRulePrice($itemProduct);


        $isTierPriceApplied = false;
        $isSpecialPriceApplied = false;
        $isCatalogRulePriceApplied = false;

        $productPriceTypes = [$tierPrice, $specialPrice];
        if(is_numeric($catalogRulePrice)){
            $productPriceTypes[] = $catalogRulePrice;
        }

        $lowestPriceType = min($productPriceTypes);

        if($lowestPriceType < $itemOriginalPrice){
            if($lowestPriceType == $tierPrice){
                $isTierPriceApplied = true;
            }elseif(($lowestPriceType == $specialPrice)
                && ($specialPrice != $tierPrice)){
                $isSpecialPriceApplied = true;
            }elseif(is_numeric($catalogRulePrice)
                && ($lowestPriceType == $catalogRulePrice)){
                $isCatalogRulePriceApplied = true;
            }
        }

        $priceTypeFlags = [
            'is_tier_price_applied' => $isTierPriceApplied,
            'is_special_price_applied' => ($isSpecialPriceApplied || $isCatalogRulePriceApplied)
        ];

        $item->setApPriceTypeFlags($priceTypeFlags);
    }

    protected function _isTierPriceAppliedOnItem($item, $itemProduct){
        $apPriceTypeFlags = $this->_getPriceTypeFlags($item, $itemProduct);
        if($apPriceTypeFlags){
            if(isset($apPriceTypeFlags['is_tier_price_applied'])
                && $apPriceTypeFlags['is_tier_price_applied']){
                return true;
            }
        }
        return false;
    }

    protected function _isSpecialPriceAppliedOnItem($item, $itemProduct){
        $apPriceTypeFlags = $this->_getPriceTypeFlags($item, $itemProduct);
        if($apPriceTypeFlags){
            if(isset($apPriceTypeFlags['is_special_price_applied'])
                && $apPriceTypeFlags['is_special_price_applied']){
                return true;
            }
        }
        return false;
    }


    protected function _oneOfPriceTypesAppliedAndShouldSkip($item, $itemProduct, $rule){
        if($this->_isTierPriceAppliedOnItem($item, $itemProduct)
            && $this->apHelper->shouldSkipTierPrice($this->getRule())){
            return true;
        }
        if($this->_isSpecialPriceAppliedOnItem($item, $itemProduct)
            && $this->apHelper->shouldSkipSpecialPrice($this->getRule())){
            return true;
        }
        return false;
    }

    protected function _getItemExpectedPricesArray($item){
        return [
            'price' => $this->apHelper->getItemPrice($item),
            'base_price' => $this->apHelper->getItemBasePrice($item),
            'original_price' => $this->apHelper->getItemOriginalPrice($item),
            'base_original_price' => $this->apHelper->getItemBaseOriginalPrice($item),
        ];
    }


    protected function _getIsDiscountAmountValid($discountAmount, $discountType){
        if(!is_numeric($discountAmount)
            || ($discountAmount <= 0)){
            return false;
        }

        if($discountType == self::DISCOUNT_TYPE_PERCENT){
            if($discountAmount > 100){
                return false;
            }
        }

        return true;
    }

    protected function _getIsDiscountTypeValid($discountType){
        if(!in_array($discountType, $this->discountTypes)){
            return false;
        }
        return true;
    }

    protected function _getDiscountAmountOnItemUnit($itemPrice, $discountType, $discountAmount){
        if($discountType == self::DISCOUNT_TYPE_PERCENT){
            return $itemPrice*($discountAmount/100);
        }elseif($discountType == self::DISCOUNT_TYPE_FIXED){
            return $discountAmount;
        }elseif($discountType == self::DISCOUNT_TYPE_FIXED_PRICE){
            if($discountAmount >= $itemPrice){
                $discountAmount = $itemPrice;
            }
            return $itemPrice-$discountAmount;
        }
        return 0;
    }

    protected function _getItemListCompDesc($itemsQtys){
        $comprehensiveDescription = "";
        $itemsCounter = 0;

        foreach($itemsQtys as $itemData){
            $itemQty = $itemData['qty'];
            $item = $itemData['item'];

            if($itemQty == 1){
                $comprehensiveDescription .=
                    sprintf(
                        "%s",
                        $item->getName()
                    );
            }elseif($itemQty > 1){
                $comprehensiveDescription .=
                    sprintf(
                        "%s of %s",
                        $itemQty,
                        $item->getName()
                    );
            }

            if(count($itemsQtys) > 1){
                if($itemsCounter == (count($itemsQtys)-2)){
                    $comprehensiveDescription .= " and ";
                }elseif($itemsCounter < (count($itemsQtys)-1)){
                    $comprehensiveDescription .= ", ";
                }
            }

            $itemsCounter++;
        }

        return $comprehensiveDescription;
    }

    protected function _getDiscountCompDesc($discountType, $discountAmount){
        $currencySymbol = $this->apHelper->getCurrentCurrencySymbol();

        $comprehensiveDescription = "";
        if($discountType == self::DISCOUNT_TYPE_PERCENT){
            if($discountAmount >= 100){
                $comprehensiveDescription .= "for free";
            }else{
                $comprehensiveDescription .=
                    sprintf(
                        "with %s%% discount",
                        $discountAmount
                    );
            }
        }elseif($this->getDiscountType() == self::DISCOUNT_TYPE_FIXED){
            $comprehensiveDescription .=
                sprintf(
                    "with %s%s discount",
                    $currencySymbol,
                    $discountAmount
                );
        }elseif($this->getDiscountType() == self::DISCOUNT_TYPE_FIXED_PRICE){
            $comprehensiveDescription .=
                sprintf(
                    "for %s%s",
                    $currencySymbol,
                    $discountAmount
                );
        }

        return $comprehensiveDescription;
    }

    protected function _setRuleApComprehensiveDescriptionLines($rule, $description, $address){
        $apDiscountDetails = $address->getApDiscountDetails();
        if(!is_array($apDiscountDetails)){
            $apDiscountDetails = [];
        }
        $apDiscountDetails[$rule->getId()]['comprehensive_description'] = $description;
        $address->setApDiscountDetails($apDiscountDetails);
    }

    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->_ruleConditionProd->loadAttributeOptions()->getAttributeOption();
        $pAttributes = [];
        $iAttributes = [];
        foreach ($productAttributes as $code => $label) {
            if (strpos($code, 'quote_item_') === 0) {
                $iAttributes[] = [
                    'value' => 'Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product|' . $code,
                    'label' => $label,
                ];
            } else {
                $pAttributes[] = [
                    'value' => 'Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product|' . $code,
                    'label' => $label,
                ];
            }
        }

        $conditions = \Magento\Rule\Model\Condition\Combine::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => 'Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine',
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Cart Item Attribute'), 'value' => $iAttributes],
                ['label' => __('Product Attribute'), 'value' => $pAttributes]
            ]
        );
        return $conditions;
    }

    public function addActionDetail($actionDetail)
    {
        $actionDetail->setRule($this->getRule());
        $actionDetail->setObject($this->getObject());
        $actionDetail->setPrefix($this->getPrefix());
        $actionDetail->setSubPrefix($this->getSubPrefix());

        $actionDetails = $this->getActionDetails();
        $actionDetails[] = $actionDetail;

        if (!$actionDetail->getId()) {
            $actionDetail->setId($this->getId() . '--' . sizeof($actionDetails));
        }

        $this->setData($this->getPrefix(), $actionDetails);
        return $this;
    }

    public function loadArray($arr, $key = 'action_details')
    {
        $this->setAggregator(
            isset($arr['aggregator']) ? $arr['aggregator'] : null
        )->setAggregatorValue(
            isset($arr['aggregator_value']) ? $arr['aggregator_value']  : null
        );

        if (!empty($arr[$key]) && is_array($arr[$key])) {
            foreach ($arr[$key] as $actionDetailArr) {
                try {
                    $actionDetail = $this->_conditionFactory->create($actionDetailArr['type']);
                    $this->addActionDetail($actionDetail);
                    $actionDetail->loadArray($actionDetailArr, $key);
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            }
        }
        return $this;
    }

    public function loadValueOptions()
    {
        return $this;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            [
                '==' => __('is'),
                '!=' => __('is not'),
                '>=' => __('equals or greater than'),
                '<=' => __('equals or less than'),
                '>' => __('greater than'),
                '<' => __('less than'),
                '()' => __('is one of'),
                '!()' => __('is not one of'),
            ]
        );
        return $this;
    }

    public function getValueElementType()
    {
        return 'text';
    }

    public function markMatchingItemsAndGetHint($items, $address){
        $this->logger->addError(sprintf(
            "The code came to markMatchingItems of Combine, the child is  %s.",
            __CLASS__
        ));
    }


    public function getAggregatorElement()
    {
        if ($this->getAggregator() === null) {
            foreach (array_keys($this->getAggregatorOption()) as $key) {
                $this->setAggregator($key);
                break;
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '_' . $this->getSubPrefix() . '__' . $this->getId() . '__aggregator',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . ']['.$this->getSubPrefix().'][' . $this->getId() . '][aggregator]',
                'values' => $this->getAggregatorSelectOptions(),
                'value' => $this->getAggregator(),
                'value_name' => $this->getAggregatorName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getAggregatorValueName()
    {
        if(isset($this->aggregatorValueOptions[$this->getAggregatorValue()])){
            return $this->aggregatorValueOptions[$this->getAggregatorValue()];
        }
        return $this->getAggregatorValue();
    }

    public function getAggregatorValueElement()
    {
        if ($this->getAggregatorValue() === null) {
            foreach (array_keys($this->aggregatorValueOptions) as $key) {
                $this->setAggregatorValue($key);
                break;
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '_' . $this->getSubPrefix() . '__' . $this->getId() . '__aggregator_value',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . ']['. $this->getSubPrefix() .'][' . $this->getId() . '][aggregator_value]',
                'values' => $this->aggregatorValueOptions,
                'value' => $this->getAggregatorValue(),
                'value_name' => $this->getAggregatorValueName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function getValueElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . ']['.$this->getSubPrefix().'][' . $this->getId() . '][value]',
            'value' => $this->getValue(),
            'values' => $this->getValueSelectOptions(),
            'value_name' => $this->getValueName(),
            'after_element_html' => $this->getValueAfterElementHtml(),
            'explicit_apply' => $this->getExplicitApply(),
            'data-form-part' => $this->getFormName()
        ];
        if ($this->getInputType() == 'date') {
            // date format intentionally hard-coded
            $elementParams['input_format'] = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
            $elementParams['date_format'] = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '_' . $this->getSubPrefix() . '__' . $this->getId() . '__value',
            $this->getValueElementType(),
            $elementParams
        )->setRenderer(
                $this->getValueElementRenderer()
            );
    }


    public function getTypeElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '_' . $this->getSubPrefix() . '__' . $this->getId() . '__type',
            'hidden',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . ']['.$this->getSubPrefix().'][' . $this->getId() . '][type]',
                'value' => $this->getType(),
                'no_span' => true,
                'class' => 'hidden',
                'data-form-part' => $this->getFormName()
            ]
        );
    }

    public function getNewChildElement()
    {
        return $this->getForm()->addField(
            $this->getPrefix() . '_' . $this->getSubPrefix() . '__' . $this->getId() . '__new_child',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . ']['.$this->getSubPrefix().'][' . $this->getId() . '][new_child]',
                'values' => $this->getNewChildSelectOptions(),
                'value_name' => $this->getNewChildName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Newchild')
            );
    }

    public function asArray(array $arrAttributes = [])
    {
        $out['aggregator'] = $this->getAggregator();
        $out['aggregator_value'] = $this->getAggregatorValue();
        $out['type'] = $this->getType();

        foreach ($this->getActionDetails() as $actionDetail) {
            $out['action_details'][] = $actionDetail->asArray();
        }

        return $out;
    }

    public function getDiscountPriceAttributeSelectOptions()
    {
        $opt = [];
        foreach ($this->discountPriceTypeOptions as $key => $value) {
            $opt[] = ['value' => $key, 'label' => $value];
        }
        return $opt;
    }

    public function getDiscountPriceTypeAttributeName()
    {
        return $this->discountPriceTypeOptions[$this->getDiscountPriceType()]->getText();
    }

    public function getDiscountPriceTypeAttributeElement()
    {
        if (null === $this->getDiscountPriceType()) {
            foreach (array_keys($this->discountPriceTypeOptions) as $option) {
                $this->setDiscountPriceType($option);
                break;
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__discount_price_type',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][discount_price_type]',
                'values' => $this->getDiscountPriceAttributeSelectOptions(),
                'value' => $this->getDiscountPriceType(),
                'value_name' => $this->getDiscountPriceTypeAttributeName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
                $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
            );
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() . __(
                'If %1 of these conditions are %2:',
                $this->getAggregatorElement()->getHtml(),
                $this->getAggregatorValueElement()->getHtml()
            );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    public function asHtmlRecursive()
    {
        $html = $this->asHtml() .
            '<ul id="' .
            $this->getPrefix() .
            '_' .
            $this->getSubPrefix() .
            '__' .
            $this->getId() .
            '__children" class="rule-param-children">';
        foreach($this->getActionDetails() as $actionDetail){
            $html .= '<li>' . $actionDetail->asHtmlRecursive() . '</li>';
        }
        $html .= '<li>' . $this->getNewChildElement()->getHtml() . '</li></ul>';
        return $html;
    }

    protected function _isValid($entity)
    {
        if (!$this->getConditions()) {
            return true;
        }

        $all = $this->getAggregator() === 'all';
        $true = (bool)$this->getAggregatorValue();

        foreach ($this->getConditions() as $cond) {
            if ($entity instanceof \Magento\Framework\Model\AbstractModel) {
                $validated = $cond->validate($entity);
            } else {
                $validated = $cond->validateByEntityId($entity);
            }
            if ($all && $validated !== $true) {
                return false;
            } elseif (!$all && $validated === $true) {
                return true;
            }
        }
        return $all ? true : false;
    }

    protected function _getItemsNamesQtysWithoutZeroQtys($itemsNamesQtys){
        $itemsNamesQtysWithoutZeroQtys = [];
        foreach($itemsNamesQtys as $itemNameQty){
            if($itemNameQty['qty'] > 0){
                $itemsNamesQtysWithoutZeroQtys[] = $itemNameQty;
            }
        }
        return $itemsNamesQtysWithoutZeroQtys;
    }

    protected function _getAlreadyDiscountedItemsExpr(
        $alreadyDiscountedItemNameSingular,
        $alreadyDiscountedItemNamePlural,
        $alreadyDiscountedItemQty,
        $discountExpr
    ){
        if($alreadyDiscountedItemQty > 0){
            return sprintf(
                "You've got %s %s %s",
                ($alreadyDiscountedItemQty == 1 ? "one" : $alreadyDiscountedItemQty),
                ($alreadyDiscountedItemQty == 1 ? $alreadyDiscountedItemNameSingular : $alreadyDiscountedItemNamePlural),
                $discountExpr
            );
        }
        return "";
    }

    protected function _getConditionFulfilmentExpr($itemsNamesQtysLeftToAddUntilDiscount, $isContinuationOfHint = true){
        $conditionFulfilmentExpr = "";
        $itemsNamesQtysLeftToAddUntilDiscountNoZeroQtys = $this->_getItemsNamesQtysWithoutZeroQtys($itemsNamesQtysLeftToAddUntilDiscount);
        if(count($itemsNamesQtysLeftToAddUntilDiscountNoZeroQtys)){
            if($isContinuationOfHint){
                $conditionFulfilmentExpr .= " ";
            }

            $conditionFulfilmentExpr .= "Add ";

            $index = 0;
            $arrayLength = count($itemsNamesQtysLeftToAddUntilDiscountNoZeroQtys);
            foreach($itemsNamesQtysLeftToAddUntilDiscountNoZeroQtys as $itemsNameQtyLeftToAddUntilDiscount){
                if($arrayLength > 1){
                    if($index == ($arrayLength-1)){
                        $conditionFulfilmentExpr .= " and ";
                    }elseif($index != 0){
                        $conditionFulfilmentExpr .= ", ";
                    }
                }
                $conditionFulfilmentExpr .= sprintf(
                    "%s more %s",
                    ($itemsNameQtyLeftToAddUntilDiscount['qty'] == 1 ? "one" : $itemsNameQtyLeftToAddUntilDiscount['qty']),
                    ($itemsNameQtyLeftToAddUntilDiscount['qty'] == 1 ? $itemsNameQtyLeftToAddUntilDiscount['hints_singular'] : $itemsNameQtyLeftToAddUntilDiscount['hints_plural'])
                );

                $index++;
            }
        }
        return $conditionFulfilmentExpr;
    }
}