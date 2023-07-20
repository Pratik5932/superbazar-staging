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
namespace Webkul\MobikulMp\Controller\Seller;

/**
 * Abstract Class AbstractB2BCustomer for adding all the required dependencies used for customer features.
 *
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
abstract class AbstractSeller extends \Webkul\MobikulApi\Controller\ApiController
{
    /**
     * Instance of MobikulCore Helper
     *
     * @var \Webkul\MobikulCore\Helper\Data
     */
    protected $mobikulHelper;

    /**
     * Instance of Emulation
     *
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulate;

    /**
     * Instance of Marketplace Helper
     *
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;
    
    /**
     * Instance of Marketplace Helper
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * Instance of Marketplace Helper
     *
     * @var \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected $urlRewriteFactory;

    /**
     * Instance of Marketplace Helper
     *
     * @var \Webkul\Marketplace\Model\Seller
     */
    protected $sellerModel;

    /**
     * Instance of SalesList collection
     *
     * @var \Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection
     */
    protected $collectionSalesList;
    
    /**
     * Initialized Dependencies
     *
     * @param \Magento\Store\Model\Store $store
     * @param \Magento\Framework\Pricing\Helper\Data $priceFormat
     * @param \Webkul\MobikulCore\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Store\Model\App\Emulation $emulate
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Webkul\Marketplace\Model\Seller $sellerModel
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Store\Model\Store $store,
        \Magento\Framework\Pricing\Helper\Data $priceFormat,
        \Webkul\MobikulCore\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
        \Webkul\MobikulCore\Helper\Data $mobikulHelper,
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\Framework\App\Action\Context $context,
        \Webkul\Marketplace\Model\Seller $sellerModel,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory $collectionSalesList
    ) {
        $this->store = $store;
        $this->priceFormat = $priceFormat;
        $this->sellerModel = $sellerModel;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->mobikulHelper = $mobikulHelper;
        $this->collectionSalesList = $collectionSalesList;
        $this->helper = $helper;
        $this->emulate = $emulate;
        $this->mpHelper = $mpHelper;
        parent::__construct($helper, $context, $jsonHelper);
    }

       /**
        * Function to strip Tags
        *
        * @param string $data data
        *
        * @return string
        */
    public function stripTags($data)
    {
        return strip_tags($data);
    }
}
