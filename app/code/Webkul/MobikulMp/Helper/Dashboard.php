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

use Magento\Sales\Model\OrderRepository;
use Magento\Catalog\Model\CategoryRepository;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory;

class Dashboard extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Image Width
     *
     * @var int $width image width
     */
    public $width;
    
    /**
     * Image Height
     *
     * @var int
     */
    public $height;

    /**
     * Seller Id
     *
     * @var int
     */
    public $sellerId;

    /**
     * Instance of Orders
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $orders;
    
    /**
     * Instance of LocationChart
     *
     * @var \Webkul\Marketplace\Block\Account\Dashboard\LocationChart
     */
    private $_locationCart;

    /**
     * Instance of Diagram
     *
     * @var \Webkul\Marketplace\Block\Account\Dashboard\Diagram
     */
    private $_salesDiagram;

    /**
     * Instance of Category Chart
     *
     * @var \Webkul\Marketplace\Block\Account\Dashboard\CategoryChart
     */
    private $_categoryChart;

    /**
     * Instance of regions
     *
     * @var \Magento\Directory\Model\Region
     */
    protected $region;

    /**
     * Instance of Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    /**
     * Instance of UrlHelper
     *
     * @var \Magento\Framework\Url
     */
    protected $urlHelper;

    /**
     * Instance of SalesList
     *
     * @var \Webkul\Marketplace\Model\Saleslist
     */
    protected $salesList;
    
    /**
     * Instance of Lists Interface
     *
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $localeList;
    
    /**
     * Instance of Magento\Sales\Model\OrderRepository
     *
     * @var OrderRepository
     */
    protected $orderRepository;
    
    /**
     * Instance of Marketplace Helper
     *
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;
    
    /**
     * Instance of Marketplace Orders
     *
     * @var \Webkul\Marketplace\Model\Orders
     */
    protected $marketplaceOrders;
    
    /**
     * Instance of CategoryRepository
     *
     * @var CategoryRepository
     */
    protected $categoryRepository;
    
    /**
     * Variable $orderItemRepository
     *
     * @var \Magento\Sales\Model\Order\ItemRepository
     */
    protected $orderItemRepository;
    
    /**
     * Variable $orderCollectionFactory
     *
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;
    
    /**
     * Variable $marketplaceFeedbackModel
     *
     * @var \Webkul\Marketplace\Model\Feedback
     */
    protected $marketplaceFeedbackModel;
    
    /**
     * Variable marketplaceDashboardHelper
     *
     * @var \Webkul\Marketplace\Helper\Dashboard\Data
     */
    protected $marketplaceDashboardHelper;
    
    /**
     * Variable marketplaceOrderResourceCollection
     *
     * @var \Webkul\Marketplace\Model\ResourceModel\Orders\Collection
     */
    protected $marketplaceOrderResourceCollection;

    /**
     * Cunstruct Function for Class Dashboard
     *
     * @param OrderRepository                                           $orderRepository
     * orderRepository
     * @param \Magento\Framework\Url                                    $urlHelper                          urlHelper
     * @param \Magento\Sales\Model\Order                                $order                              order
     * @param CategoryRepository                                        $categoryRepository
     * categoryRepository
     * @param \Magento\Directory\Model\Region                           $region                             region
     * @param CollectionFactory                                         $orderCollectionFactory
     * orderCollectionFactory
     * @param \Magento\Framework\App\Request\Http                       $request                            request
     * @param \Webkul\Marketplace\Model\Saleslist                       $salesList                          salesList
     * @param \Magento\Framework\App\Helper\Context                     $context                            context
     * @param \Webkul\Marketplace\Helper\Data                           $marketplaceHelper
     * marketplaceHelper
     * @param \Webkul\Marketplace\Model\Orders                          $marketplaceOrders
     * marketplaceOrders
     * @param \Magento\Framework\Locale\ListsInterface                  $localeList                         localeList
     * @param \Webkul\Marketplace\Model\Feedback                        $marketplaceFeedbackModel
     * marketplaceFeedbackModel
     * @param \Magento\Sales\Model\Order\ItemRepository                 $orderItemRepository
     * orderItemRepository
     * @param \Webkul\Marketplace\Helper\Dashboard\Data                 $marketplaceDashboardHelper
     * marketplaceDashboardHelper
     * @param \Webkul\Marketplace\Model\ResourceModel\Orders\Collection $marketplaceOrderResourceCollection
     * marketplaceOrderResourceCollection
     *
     * @return void
     */
    public function __construct(
        OrderRepository $orderRepository,
        \Magento\Framework\Url $urlHelper,
        \Magento\Sales\Model\Order $order,
        CategoryRepository $categoryRepository,
        \Magento\Directory\Model\Region $region,
        CollectionFactory $orderCollectionFactory,
        \Magento\Framework\App\Request\Http $request,
        \Webkul\Marketplace\Model\Saleslist $salesList,
        \Magento\Framework\App\Helper\Context $context,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\Marketplace\Model\Orders $marketplaceOrders,
        \Magento\Framework\Locale\ListsInterface $localeList,
        \Webkul\Marketplace\Model\Feedback $marketplaceFeedbackModel,
        \Magento\Sales\Model\Order\ItemRepository $orderItemRepository,
        \Webkul\Marketplace\Helper\Dashboard\Data $marketplaceDashboardHelper,
        \Webkul\Marketplace\Block\Account\Dashboard\LocationChart $locationCart,
        \Webkul\Marketplace\Block\Account\Dashboard\Diagrams $salesDiagram,
        \Webkul\Marketplace\Block\Account\Dashboard\CategoryChart $categoryChart,
        \Webkul\Marketplace\Model\ResourceModel\Orders\Collection $marketplaceOrderResourceCollection
    ) {
        $this->orders = $order;
        $this->region = $region;
        $this->request = $request;
        $this->urlHelper = $urlHelper;
        $this->salesList = $salesList;
        $this->localeList = $localeList;
        $this->_salesDiagram = $salesDiagram;
        $this->_locationCart = $locationCart;
        $this->_categoryChart = $categoryChart;
        $this->orderRepository = $orderRepository;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->marketplaceOrders = $marketplaceOrders;
        $this->categoryRepository = $categoryRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->marketplaceFeedbackModel = $marketplaceFeedbackModel;
        $this->marketplaceDashboardHelper = $marketplaceDashboardHelper;
        $this->marketplaceOrderResourceCollection = $marketplaceOrderResourceCollection;
        parent::__construct($context);
    }

    /**
     * Location Cart Class
     *
     * @return \Webkul\Marketplace\Block\Account\Dashboard\LocationChart
     */
    public function getLocationCartBlock()
    {
        return $this->_locationCart;
    }

    /**
     * Sales Diagram Class
     *
     * @return \Webkul\Marketplace\Block\Account\Dashboard\LocationChart
     */
    public function getDiagramBlock()
    {
        return $this->_salesDiagram;
    }
    
    /**
     * Sales Diagram Class
     *
     * @return \Webkul\Marketplace\Block\Account\Dashboard\LocationChart
     */
    public function getCategoryChartBlock()
    {
        return $this->_categoryChart;
    }

    /**
     * Function to get categories with most sold products
     *
     * @return array category Data
     */
    public function getTopSaleCategories()
    {
        $collection = $this->orderCollectionFactory->create()
            ->addFieldToFilter("seller_id", $this->sellerId)
            ->addFieldToFilter("parent_item_id", ["null"=>"true"])
            ->getAllOrderProducts();
        $name       = "";
        $catArr     = [];
        $resultData = [];
        $totalOrderedProducts = 0;
        foreach ($collection as $coll) {
            $totalOrderedProducts = $totalOrderedProducts + $coll["qty"];
        }
        $collection = $this->orderCollectionFactory->create()
            ->addFieldToFilter("seller_id", $this->sellerId)
            ->addFieldToFilter("parent_item_id", ["null"=>"true"]);
        foreach ($collection as $coll) {
            $item    = $this->orderItemRepository->get($coll["order_item_id"]);
            $product = $item->getProduct();
            if ($product) {
                $productCategories = $product->getCategoryIds();
                if (isset($productCategories[0])) {
                    if (!isset($catArr[$productCategories[0]])) {
                        $catArr[$productCategories[0]] = $coll["magequantity"];
                    } else {
                        $catArr[$productCategories[0]] = $catArr[$productCategories[0]] + $coll["magequantity"];
                    }
                }
            }
        }
        $categoryArr   = [];
        $percentageArr = [];
        if ($totalOrderedProducts > 0) {
            foreach ($catArr as $key => $value) {
                $categoryArr[$key]   = $this->categoryRepository->get($key)->getName();
                $percentageArr[$key] = round((($value * 100) / $totalOrderedProducts), 2);
            }
        }
        $resultData["category_arr"]   = $categoryArr;
        $resultData["percentage_arr"] = $percentageArr;
        return $resultData;
    }

    /**
     * Function to get Order Collection
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getCollection()
    {
        $orderids   = $this->getOrderIdsArray($this->sellerId, "");
        $ids        = $this->getEntityIdsArray($orderids);
        $collection = $this->orderCollectionFactory->create()
            ->addFieldToSelect("*")
            ->addFieldToFilter("entity_id", ["in"=>$ids])
            ->setOrder("created_at", "desc")
            ->setPageSize(5);
        return $collection;
    }

    /**
     * Function to get array of Order Ids
     *
     * @param int    $customerId        customer id
     * @param string $filterOrderstatus filterOrderstatus
     *
     * @return array $id
     */
    public function getOrderIdsArray($customerId = "", $filterOrderstatus = "")
    {
        $orderids         = [];
        $collectionOrders = $this->orderCollectionFactory->create()
            ->addFieldToFilter("seller_id", $this->sellerId)
            ->addFieldToSelect("order_id")
            ->distinct(true);
        foreach ($collectionOrders as $collectionOrder) {
            $tracking = $this->getOrderinfo($collectionOrder->getOrderId());
            if ($tracking) {
                if ($filterOrderstatus) {
                    if ($tracking->getIsCanceled()) {
                        if ($filterOrderstatus == "canceled") {
                            array_push($orderids, $collectionOrder->getOrderId());
                        }
                    } else {
                        $tracking = $this->orderRepository->create($collectionOrder->getOrderId());
                        if ($tracking->getStatus() == $filterOrderstatus) {
                            array_push($orderids, $collectionOrder->getOrderId());
                        }
                    }
                } else {
                    array_push($orderids, $collectionOrder->getOrderId());
                }
            }
        }
        return $orderids;
    }

    /**
     * Function to get array of Entity Ids
     *
     * @param array $orderids array of order ids
     *
     * @return array $id
     */
    public function getEntityIdsArray($orderids = [])
    {
        $ids = [];
        foreach ($orderids as $orderid) {
            $collectionIds = $this->orderCollectionFactory->create()
                ->addFieldToFilter("order_id", $orderid)
                ->setOrder("entity_id", "DESC")
                ->setPageSize(1);
            foreach ($collectionIds as $collectionId) {
                $autoid = $collectionId->getId();
                array_push($ids, $autoid);
            }
        }
        return $ids;
    }

    /**
     * Function to get OrderInfo by Order Id
     *
     * @param int $orderId order Id
     *
     * @return array $data
     */
    public function getOrderinfo($orderId = "")
    {
        $data  = [];
        $model = $this->marketplaceOrders
            ->getCollection()
            ->addFieldToFilter("seller_id", $this->sellerId)
            ->addFieldToFilter("order_id", $orderId);
        $salesOrder = $this->marketplaceOrderResourceCollection->getTable("sales_order");
        $model->getSelect()
            ->join(
                $salesOrder." as so",
                "main_table.order_id=so.entity_id",
                ["order_approval_status"=>"order_approval_status"]
            )->where("so.order_approval_status=1");
        foreach ($model as $tracking) {
            $data = $tracking;
        }
        return $data;
    }

    /**
     * Function to get Main Order
     *
     * @param int $orderId orderId
     *
     * @return array $order data
     */
    public function getMainOrder($orderId)
    {
        $collection = $this->orders
            ->getCollection()
            ->addFieldToFilter("entity_id", $orderId);
        foreach ($collection as $res) {
            return $res;
        }
        return [];
    }

    /**
     * Function to get Product Name By Order Id
     *
     * @param int $orderId orderId
     *
     * @return string Product Name
     */
    public function getpronamebyorder($orderId)
    {
        $collection = $this->orderCollectionFactory->create()
            ->addFieldToFilter("seller_id", $this->sellerId)
            ->addFieldToFilter("order_id", $orderId);
        $productNames = [];
        foreach ($collection as $res) {
            $eachProductName              = [];
            $item                         = $this->orderItemRepository->get($res->getOrderItemId());
            $eachProductName["qty"]       = (int) $res["magequantity"];
            $eachProductName["name"]      = $res["magepro_name"];
            $eachProductName["productId"] = 0;
            if ($item->getProduct()) {
                $eachProductName["productId"] = $item->getProduct()->getId();
            }
            $productNames[] = $eachProductName;
        }
        return $productNames;
    }

    /**
     * Function to get Price of Order By Order Id
     *
     * @param int $orderId orderId
     *
     * @return int|float
     */
    public function getPricebyorder($orderId)
    {
        $collection = $this->salesList->getCollection()
            ->addFieldToFilter(
                'main_table.seller_id',
                $this->sellerId
            )->addFieldToFilter(
                'main_table.order_id',
                $orderId
            )->getPricebyorderData();
        $name = '';
        $actualSellerAmount = 0;
        foreach ($collection as $coll) {
            // calculate order actual_seller_amount in base currency
            $appliedCouponAmount = $coll['applied_coupon_amount']*1;
            $shippingAmount = $coll['shipping_charges']*1;
            $refundedShippingAmount = $coll['refunded_shipping_charges']*1;
            $totalshipping = $shippingAmount - $refundedShippingAmount;
            $vendorTaxAmount = $coll['total_tax']*1;
            if ($coll['actual_seller_amount'] * 1) {
                $taxShippingTotal = $vendorTaxAmount + $totalshipping - $appliedCouponAmount;
                $actualSellerAmount += $coll['actual_seller_amount'] + $taxShippingTotal;
            } else {
                if ($totalshipping * 1) {
                    $actualSellerAmount += $totalshipping - $appliedCouponAmount;
                }
            }
        }
        return $actualSellerAmount;
    }
    
    /**
     * Function to get Ordered price By Order
     *
     * @param \Magento\Sales\Model\Order $order     object of Order
     * @param float                      $basePrice basePrice
     *
     * @return float ordered Price
     */
    public function getOrderedPricebyorder($order, $basePrice)
    {
        $currentCurrencyCode = $order->getOrderCurrencyCode();
        $baseCurrencyCode = $order->getBaseCurrencyCode();
        $allowedCurrencies = $this->marketplaceHelper->getConfigAllowCurrencies();
        $rates = $this->marketplaceHelper->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
        if (empty($rates[$currentCurrencyCode])) {
            $rates[$currentCurrencyCode] = 1;
        }
        return $basePrice * $rates[$currentCurrencyCode];
    }

    /**
     * Function to get Review Collection
     *
     * @return \Webkul\Marketplace\Model\Feedback $collection
     */
    public function getReviewcollection()
    {
        $collection = $this->marketplaceFeedbackModel
            ->getCollection()
            ->addFieldToFilter("seller_id", $this->sellerId)
            ->addFieldToFilter("status", 1)
            ->setOrder("created_at", "desc")
            ->setPageSize(5)
            ->setCurPage(1);
        return $collection;
    }
}
