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

namespace Webkul\MobikulMp\Observer;

abstract class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Mobikul Helper Data
     *
     * @var \Webkul\MobikulCore\Helper\Data
     */
    protected $helper;

    /**
     * Device Token Mode
     *
     * @var \Webkul\MobikulCore\Model\DeviceToken
     */
    protected $deviceToken;

    /**
     * Store Manager Interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Catalog Produtc Factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $magentoProduct;

    /**
     * Seller Collection
     *
     * @var \Webkul\Marketplace\Model\ResourceModel\Seller\Collection
     */
    protected $sellerCollection;

    /**
     * Image Factory
     *
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $imageHelperFactory;

    /**
     * Marketplace Product
     *
     * @var \Webkul\Marketplace\Model\Product
     */
    protected $marketplaceProduct;

    /**
     * Construct Functin for Abstract Class AbstractObserver
     *
     * @param \Webkul\Mobikul\Helper\Data                               $helper             mobikul helper
     * @param \Webkul\Mobikul\Model\DeviceToken                         $deviceToken        deviceTokens
     * @param \Magento\Framework\Json\Helper\Data                       $jsonHelper         jsonHelpers
     * @param \Magento\Catalog\Model\ProductFactory                     $magentoProduct     magentoProduct
     * @param \Webkul\Marketplace\Model\Product                         $marketplaceProduct marketplaceProduct
     * @param \Magento\Catalog\Helper\ImageFactory                      $imageHelperFactory imageHelperFactory
     * @param \Magento\Store\Model\StoreManagerInterface                $storeManager       storeManager
     * @param \Webkul\Marketplace\Model\ResourceModel\Seller\Collection $sellerCollection   sellerCollection
     *
     * @return void
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Webkul\MobikulCore\Model\DeviceToken $deviceToken,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\ProductFactory $magentoProduct,
        \Webkul\Marketplace\Model\Product $marketplaceProduct,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Marketplace\Model\ResourceModel\Seller\Collection $sellerCollection,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        $this->helper             = $helper;
        $this->jsonHelper         = $jsonHelper;
        $this->deviceToken        = $deviceToken;
        $this->storeManager       = $storeManager;
        $this->magentoProduct     = $magentoProduct;
        $this->sellerCollection   = $sellerCollection;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->marketplaceProduct = $marketplaceProduct;
        $this->curl = $curl;
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
