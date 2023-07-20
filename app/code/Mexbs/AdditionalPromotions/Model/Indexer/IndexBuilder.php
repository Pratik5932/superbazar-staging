<?php
namespace Mexbs\AdditionalPromotions\Model\Indexer;

use Magento\Catalog\Model\Product;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\Framework\App\ResourceConnection;


class IndexBuilder
{
    const SECONDS_IN_DAY = 86400;


    protected $metadataPool;
    protected $_catalogRuleGroupWebsiteColumnsList = ['rule_id', 'customer_group_id', 'website_id'];
    protected $resource;
    protected $ruleCollectionFactory;
    protected $logger;
    protected $productFactory;
    protected $ruleFactory;
    protected $loadedProducts;
    protected $batchCount;
    private $helper;
    protected $connection;
    protected $productCollectionFactory;
    protected $catalogProductVisibility;
    protected $productOption;

    public function __construct(
        RuleCollectionFactory $ruleCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Mexbs\AdditionalPromotions\Helper\Data $helper,
        \Magento\Catalog\Model\Product\Option $productOption,
        $batchCount = 1000
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->logger = $logger;
        $this->productFactory = $productFactory;
        $this->ruleFactory = $ruleFactory;
        $this->batchCount = $batchCount;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->helper = $helper;
        $this->productOption = $productOption;
    }

    protected function isProductHasCustomOptions($product){
        return count($this->productOption->getProductOptionCollection($product)) > 0;
    }

    protected function deleteProductsForRule($ruleId, $productIds=null){
        if($productIds === null){
            $this->connection->delete(
                $this->resource->getTableName('apactionrule_product'),
                $this->connection->quoteInto('rule_id=?', $ruleId)
            );
        }else{
            $this->connection->delete(
                $this->resource->getTableName('apactionrule_product'),
                sprintf("rule_id='%s' AND product_id IN ('%s')", $ruleId, implode("','",$productIds))
            );
        }
    }

    protected function getProductIdsWithTierPrices(){
        $query = $this->connection->select()
            ->from(['e' => $this->resource->getTableName('catalog_product_entity')], 'e.entity_id')
            ->joinInner(
                ['t' => $this->resource->getTableName('catalog_product_entity_tier_price')],
                sprintf("e.entity_id = t.entity_id"),
                []
            )->group('e.entity_id');
        $resultArrays =  $this->connection->fetchAll($query);
        $ids = [];
        foreach($resultArrays as $resultArray){
            $ids[] = $resultArray['entity_id'];
        }
        return $ids;
    }

    public function reindexByProductIdsRuleIds($productIds = null, $ruleIds = null){
        try {
            /**
             * @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection $rulesCollection
             */
            $rulesCollection = $this->ruleCollectionFactory->create();
            $rulesCollection->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('display_promo_block', 1);

            if($ruleIds != null){
                $rulesCollection->addFieldToFilter('rule_id', ['in' => $ruleIds]);
            }
            foreach($rulesCollection as $rule){
                /**
                 * @var \Magento\SalesRule\Model\Rule $rule
                 */
                $ruleId = $rule->getId();

                if (!$rule->getIsActive()
                    || !$rule->getDisplayPromoBlock()
                    || !$this->helper->isSimpleActionAp($rule->getSimpleAction())) {
                    $this->deleteProductsForRule($ruleId);
                    continue;
                }

                $websiteIds = $rule->getWebsiteIds();
                if (!is_array($websiteIds)) {
                    $websiteIds = explode(',', $websiteIds);
                }
                if (empty($websiteIds)) {
                    $this->deleteProductsForRule($ruleId);
                    continue;
                }

                $ruleActionDetail = $this->helper->getLoadedActionDetail($rule);
                if($ruleActionDetail->hasAddressConditionsInActionInAnyGroup()){
                    $this->deleteProductsForRule($ruleId);
                    continue;
                }

                $this->deleteProductsForRule($ruleId, $productIds);

                $nonEmptyGroupNumbers = $ruleActionDetail->getNonEmptyGroupNumbers();
                $rows = [];

                foreach($nonEmptyGroupNumbers as $groupNumber){
                    /**
                     * @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection
                     */
                    $productCollection = $this->productCollectionFactory->create();

                    if($productIds !== null){
                        $productCollection->addFieldToFilter('entity_id', ['in' => $productIds]);
                    }

                    $productCollection->addWebsiteFilter($rule->getWebsiteIds())
                        ->addFieldToFilter("type_id", [
                            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                            \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
                            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
                            \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                        ])
                        ->addFieldToFilter("status", \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

                    if($this->helper->shouldSkipTierPrice($rule)){
                        $productIdsWithTierPrices = $this->getProductIdsWithTierPrices();
                        $productCollection->addFieldToFilter('entity_id', ['nin' => $productIdsWithTierPrices]);
                    }

                    if($this->helper->shouldSkipSpecialPrice($rule)){
                        $productCollection->addAttributeToFilter('special_price', ['null' => true], 'left');
                    }

                    $productCollection->addOptionsToResult();
                    $ruleActionDetail->collectValidatedAttributesForGroup($productCollection, $groupNumber);


                    foreach($productCollection as $product){
                        /**
                         * @var \Magento\Catalog\Model\Product $product
                         */
                        if($ruleActionDetail->validateProductForGroupWithoutQuote($groupNumber, $product)){
                            $rows[] = [
                                'rule_id' => $ruleId,
                                'group_number' => $groupNumber,
                                'product_id' => $product->getId(),
                                'product_has_custom_options' => ($this->isProductHasCustomOptions($product) ? 1 : 0)
                            ];

                            if (count($rows) == $this->batchCount) {
                                $this->connection->insertMultiple($this->resource->getTableName('apactionrule_product'), $rows);
                                $rows = [];
                            }
                        }
                    }
                }

                if (!empty($rows)) {
                    $this->connection->insertMultiple($this->resource->getTableName('apactionrule_product'), $rows);
                }
            }
        } catch (\Exception $e) {
            $this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
            __("Additional Promotions Promo Blocks indexing failed. See details in exception log.")
            );
        }
    }

    public function deleteFromIndexByProductIds($productIds){
        $query = $this->connection->deleteFromSelect(
            $this->connection
                ->select()
                ->from($this->resource->getTableName('apactionrule_product'), 'product_id')
                ->distinct()
                ->where('product_id IN (?)', $productIds),
            $this->resource->getTableName('apactionrule_product')
        );
        $this->connection->query($query);
    }

    protected function updateRuleProductData(Rule $rule)
    {
        if(!$this->helper->isSimpleActionAp($rule->getSimpleAction())){
            return $this;
        }

        $ruleId = $rule->getId();

        $this->connection->delete(
            $this->resource->getTableName('apactionrule_product'),
            $this->connection->quoteInto('rule_id=?', $ruleId)
        );

        if (!$rule->getIsActive()
            || !$rule->getDisplayPromoBlock()) {
            return $this;
        }

        $websiteIds = $rule->getWebsiteIds();
        if (!is_array($websiteIds)) {
            $websiteIds = explode(',', $websiteIds);
        }
        if (empty($websiteIds)) {
            return $this;
        }

        $ruleActionDetail = $this->helper->getLoadedActionDetail($rule);
        if($ruleActionDetail->hasAddressConditionsInActionInAnyGroup()){
            return $this;
        }

        $nonEmptyGroupNumbers = $ruleActionDetail->getNonEmptyGroupNumbers();

        $rows = [];

        foreach($nonEmptyGroupNumbers as $groupNumber){
            /**
             * @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection
             */
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addWebsiteFilter($rule->getWebsiteIds())
                ->addFieldToFilter("type_id", [
                    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                    \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
                    \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
                    \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                ]);

            if($this->helper->shouldSkipTierPrice($rule)){
                $productIdsWithTierPrices = $this->getProductIdsWithTierPrices();
                $productCollection->addFieldToFilter('entity_id', ['nin' => $productIdsWithTierPrices]);
            }

            if($this->helper->shouldSkipSpecialPrice($rule)){
                $productCollection->addAttributeToFilter('special_price', ['null' => true], 'left');
            }

            $productCollection->addOptionsToResult();
            $ruleActionDetail->collectValidatedAttributesForGroup($productCollection, $groupNumber);


            foreach($productCollection as $product){
                /**
                 * @var \Magento\Catalog\Model\Product $product
                 */
                if($ruleActionDetail->validateProductForGroupWithoutQuote($groupNumber, $product)){
                    $rows[] = [
                        'rule_id' => $ruleId,
                        'group_number' => $groupNumber,
                        'product_id' => $product->getId(),
                        'product_has_custom_options' => ($this->isProductHasCustomOptions($product) ? 1 : 0)
                    ];

                    if (count($rows) == $this->batchCount) {
                        $this->connection->insertMultiple($this->resource->getTableName('apactionrule_product'), $rows);
                        $rows = [];
                    }
                }
            }
        }

        if (!empty($rows)) {
            $this->connection->insertMultiple($this->resource->getTableName('apactionrule_product'), $rows);
        }

        return $this;
    }

    /**
     * @param \Exception $e
     * @return void
     */
    protected function critical($e)
    {
        $this->logger->critical($e);
    }
}
