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
namespace Webkul\MobikulMp\Controller\Marketplace;

/**
 * Class Dashboard controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class Dashboard extends AbstractMarketplace
{
    /**
     * Execute Function for class Dashboard @need to optimize/update for creating separate pai for chats
     *
     * @return string json data
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "DASHBOARD".$this->width.$this->storeId.$this->mFactor;
            $cacheString .= $this->customerToken.$this->customerId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $this->customerSession->setCustomerId($this->customerId);
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            // world based sales calculation images /////////////////////////////////////////////////////////
            $this->dashboardHelper->sellerId = $this->customerId;
            $this->dashboardHelper->width    = $this->width * $this->mFactor < 500 ? $this->width*$this->mFactor : 500;
            $this->dashboardHelper->height   = (
                $this->dashboardHelper->width / 2
            ) * $this->mFactor;

            $locationCartBlock = $this->dashboardHelper->getLocationCartBlock();
            $this->returnArray[
                "dailySalesLocationReport"
            ]   = $locationCartBlock->getSellerStatisticsGraphUrl(
                "day"
            );
            $this->returnArray[
                "yearlySalesLocationReport"
            ]  = $locationCartBlock->getSellerStatisticsGraphUrl(
                "year"
            );
            $this->returnArray[
                "monthlySalesLocationReport"
            ] = $locationCartBlock->getSellerStatisticsGraphUrl(
                "month"
            );
            $this->returnArray[
                "weeklySalesLocationReport"
            ] = $locationCartBlock->getSellerStatisticsGraphUrl(
                "week"
            );
            // date wise sales chart images /////////////////////////////////////////////////////////////////
            
            $salesDiagramBlock = $this->dashboardHelper->getDiagramBlock();
            // date wise sales chart images /////////////////////////////////////////////////////////////////
           
            $this->returnArray["salesStats"]   = $salesDiagramBlock->getSellerStatisticsGraphUrl(true);
            $this->returnArray["dailySalesStats"]   = $salesDiagramBlock->getSellerStatisticsGraphUrl("day");
            $this->returnArray["weeklySalesStats"]  = $salesDiagramBlock->getSellerStatisticsGraphUrl("week");
            $this->returnArray["yearlySalesStats"]  = $salesDiagramBlock->getSellerStatisticsGraphUrl("year");
            $this->returnArray["monthlySalesStats"] = $salesDiagramBlock->getSellerStatisticsGraphUrl("month");

            // calculating amount data for seller ///////////////////////////////////////////////////////////
            $this->getSellerPayData();
            // getting top selling products /////////////////////////////////////////////////////////////////
            $this->getSellerTopSellingProducts();
            // getting category chart image /////////////////////////////////////////////////////////////////
            $categoryChartBlock = $this->dashboardHelper->getCategoryChartBlock();
            $this->returnArray["categoryChart"] = $categoryChartBlock->getSellerStatisticsGraphUrl();
            // getting latest order history /////////////////////////////////////////////////////////////////
            $this->getSellerRecentOrderList();
            // getting latest order history /////////////////////////////////////////////////////////////////
            if ($this->marketplaceHelper->getSellerProfileDisplayFlag()) {
                $rate             = [];
                $ratings          = [];
                $products         = [];
                $reviewList       = [];
                $reviewcollection = $this->dashboardHelper->getReviewcollection();
                foreach ($reviewcollection as $keyed) {
                    $eachReview                  = [];
                    $eachReview["name"]          = $this->customer->load(
                        $keyed->getBuyerId()
                    )->getName();
                    $eachReview["date"]          = $keyed["created_at"];
                    $eachReview["comment"]       = $keyed["feed_review"];
                    $eachReview["priceRating"]   = ceil($keyed["feed_price"]);
                    $eachReview["valueRating"]   = ceil($keyed["feed_value"]);
                    $eachReview["qualityRating"] = ceil($keyed["feed_quality"]);
                    $reviewList[]                = $eachReview;
                }
                $this->returnArray["reviewList"]       = $reviewList;
            }
            $this->returnArray["success"]                    = true;
            
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            $this->checkNGenerateEtag($cacheString);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray, 1);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->eTag          = $this->wholeData["eTag"]          ?? "";
            $this->width         = $this->wholeData["width"]         ?? 1000;
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
            $this->mFactor       = $this->wholeData["mFactor"]       ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->customerId    = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            $isSeller = $this->mpSeller->getCollection()
                ->addFieldToFilter('seller_id', ['eq'=> $this->customerId])
                ->addFieldToFilter('store_id', ['in'=> [$this->storeId, 0]])->getSize();

            if ($isSeller == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('invalid seller')
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    /**
     * Function to get seller Payment data
     *
     * @return void
     */
    public function getSellerPayData()
    {
        $totalSaleColl = $this->saleperPartner
            ->getCollection()
            ->addFieldToFilter("seller_id", $this->customerId);
        $totalSale       = 0;
        $totalRemainSale = 0;
        foreach ($totalSaleColl as $value) {
            $totalSale       = $value->getAmountReceived();
            $totalRemainSale = $value->getAmountRemain();
        }
        $this->returnArray["totalPayout"]     = $this->helperCatalog->stripTags(
            $this->checkoutHelper->formatPrice(
                $totalSale
            )
        );
        $this->returnArray["lifetimeSale"]    = $this->helperCatalog->stripTags(
            $this->checkoutHelper->formatPrice(
                $totalSale + $totalRemainSale
            )
        );
        $this->returnArray["remainingAmount"] = $this->helperCatalog->stripTags(
            $this->checkoutHelper->formatPrice(
                $totalRemainSale
            )
        );
    }

    /**
     * Function to  get Seller's top sold products
     *
     * @return void
     */
    public function getSellerTopSellingProducts()
    {
        $topSaleProductColl = $this->orderCollectionFactory
            ->create()
            ->addFieldToFilter("seller_id", $this->customerId)
            ->addFieldToFilter("parent_item_id", ["null"=>"true"])
            ->getAllOrderProducts();
        $name        = "";
        $resultData  = [];
        foreach ($topSaleProductColl as $coll) {
            $item    = $this->orderItemRepository->get($coll["order_item_id"]);
            $product = $item->getProduct();
            if ($product) {
                $productData = $this->helperCatalog->getOneProductRelevantData(
                    $product,
                    $this->storeId,
                    $this->width,
                    $this->customerId
                );
                $productData["salesQty"] = $coll['qty'];
                $resultData[] =$productData;
            }
        }
        $this->returnArray["topSellingProducts"] = $resultData;
    }

    /**
     * Function to get recent order list of seller
     *
     * @return void
     */
    public function getSellerRecentOrderList()
    {
        $orderCollection = $this->dashboardHelper->getCollection();
        $recentOrderList = [];
        foreach ($orderCollection as $res) {
            $order    = $this->dashboardHelper->getMainOrder($res["order_id"]);
            $status   = $order->getStatus();
            $name     = $order->getCustomerName();
            $state    = $order->getState();
            
            $tracking = $this->marketplaceOrderhelper->getOrderinfo(
                $res["order_id"]
            );
            if ($tracking && $tracking->getId() && $tracking->getIsCanceled()) {
                $status = "Canceled";
            }
            $eachOrder                 = [];
            $eachOrder["orderId"]      = $res["order_id"];
            $eachOrder["incrementId"]  = $res["magerealorder_id"];
            
            $item = $this->orderItemRepository->get($res->getOrderItemId());
            
            $eachOrder["qtyOrdered"] = (int)$item['qty_ordered'];
            $eachOrder["qtyInvoiced"] = (int)$item['qty_invoiced'];
            $eachOrder["qtyShipped"] = (int)$item['qty_shipped'];
            $eachOrder["qtyCanceled"] = (int)$item['qty_canceled'];
            $eachOrder["qtyRefunded"] = (int)$item['qty_refunded'];
            $eachOrder["productNames"] = $this->dashboardHelper->getpronamebyorder($res["order_id"]);
            $eachOrder["status"]       = strtoupper($status);
            $eachOrder["statusColorCode"] = $this->helper->getOrderStatusColorCode($status);
            if ($this->marketplaceHelper->getSellerProfileDisplayFlag()) {
                $eachOrder["customerDetails"]["name"]= $name;
                $eachOrder["customerDetails"]["date"]= $this->viewTemplate->formatDate(
                    $res["created_at"]
                );
                $orderPrice= $this->dashboardHelper->getPricebyorder(
                    $res["order_id"]
                );
                $eachOrder["customerDetails"]["baseTotal"]= $this->helperCatalog->stripTags(
                    $order->formatBasePrice(
                        $orderPrice
                    )
                );
                $eachOrder["customerDetails"]["purchaseTotal"] = $this->helperCatalog->stripTags(
                    $order->formatPrice(
                        $this->dashboardHelper->getOrderedPricebyorder(
                            $order,
                            $orderPrice
                        )
                    )
                );
            }
            $recentOrderList[] = $eachOrder;
        }
        $this->returnArray["recentOrderList"] = $recentOrderList;
    }
}
