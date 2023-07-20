<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulMp
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\MobikulMp\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Instance of Marketplace Seller
     *
     * @var \Webkul\Marketplace\Model\Seller $seller seller
     */
    protected $seller;

    /**
     * Instance of Marketplace helper
     *
     * @var \Webkul\Marketplace\Helper\data $mpHelper mpHelper
     */
    protected $mpHelper;

    /**
     * Instance of Store Manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager storeManager
     */
    protected $storeManager;

    /**
     * Construct function for Helper Data Mobikul Marketplace
     *
     * @param \Webkul\Marketplace\Model\Seller           $seller       seller
     * @param \Webkul\Marketplace\Helper\data            $mpHelper     mpHelper
     * @param \Magento\Framework\App\Helper\Context      $context      context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager storeManager
     */
    public function __construct(
        \Webkul\Marketplace\Model\Seller $seller,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->seller        = $seller;
        $this->mpHelper      = $mpHelper;
        $this->storeManager  = $storeManager;
        parent::__construct($context);
    }

    /**
     * Function to verify if the customer is Seller
     *
     * @param int $customerId customer Id
     *
     * @return bool seller Status
     */
    public function isSeller($customerId)
    {
        $sellerStatus = 0;
        $model = $this->getSellerCollectionObj($customerId);
        foreach ($model as $value) {
            $sellerStatus = $value->getIsSeller();
        }
        return $sellerStatus;
    }

    /**
     * Function to get Seller Collection Object
     *
     * @param integer $customerId customer Id
     *
     * @return Webkul\Marketplace\Model\Seller
     */
    public function getSellerCollectionObj($customerId)
    {
        $model = $this->seller->getCollection()
            ->addFieldToFilter("seller_id", $customerId)
            ->addFieldToFilter("store_id", $this->storeManager->getStore()->getStoreId());
            // If seller data doesn't exist for current store ////////////////////////////
        if (!count($model)) {
            $model = $this->seller->getCollection()
                ->addFieldToFilter("seller_id", $customerId)
                ->addFieldToFilter("store_id", 0);
        }
        return $model;
    }

    /**
     * Function to get allowe category Ids
     *
     * @param integer $sellerId Seller Id
     *
     * @return string
     */
    public function getAllowedCategoryIds($sellerId)
    {
        $seller = $this->mpHelper->getSellerDataBySellerId($sellerId)->setPageSize(1)->getFirstItem()->getData();
        if (!empty($seller["allowed_categories"])) {
            return $seller["allowed_categories"];
        } else {
            return $this->scopeConfig->getValue(
                "marketplace/product_settings/categoryids",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
    }

    /**
     * Function to add Upsell product Status
     *
     * @return bool
     */
    public function addUpsellProductStatus()
    {
        return $this->scopeConfig->getValue(
            "marketplace/product_settings/allow_upsell_product",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Function to get the weight unit from admin configuration
     *
     * @return string
     */
    public function getWeightUnit() : string
    {
        return $this->scopeConfig->getValue(
            "marketplace/product_settings/allow_upsell_product",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Function to add related product Status
     *
     * @return bool
     */
    public function addRelatedProductStatus()
    {
        return $this->scopeConfig->getValue(
            "marketplace/product_settings/allow_related_product",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Function to add Cross Sell product Status
     *
     * @return bool
     */
    public function addCrosssellProductStatus()
    {
        return $this->scopeConfig->getValue(
            "marketplace/product_settings/allow_crosssell_product",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Fucntion to verify if the string is jSon
     *
     * @param string $string string
     *
     * @return bool
     */
    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
