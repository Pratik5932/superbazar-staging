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
 * Class OrderList controller

 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class OrderList extends AbstractMarketplace
{

    /**
     * Execute function for class OrderList
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "ORDERLIST".$this->storeId.$this->dateTo.$this->dateFrom.$this->status;
            $cacheString .= $this->incrementId.$this->pageNumber.$this->customerToken.$this->customerId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $orderIds    = $this->getOrderIdsArray($this->customerId, $this->status);
            $ids         = $this->getEntityIdsArray($orderIds);
            $this->dashboardHelper->sellerId = $this->customerId;
            $orderCollection = $this->orderCollectionFactory->create()
                ->addFieldToSelect("*")
                ->addFieldToFilter("entity_id", ["in"=>$ids]);
            $to   = null;
            $from = null;
            if ($this->dateTo) {
                $todate = date_create($this->dateTo);
                $to     = date_format($todate, "Y-m-d 23:59:59");
            }
            if ($this->dateFrom) {
                $fromdate = date_create($this->dateFrom);
                $from     = date_format($fromdate, "Y-m-d H:i:s");
            }
            if ($this->incrementId) {
                $orderCollection->addFieldToFilter("magerealorder_id", ["like"=>"%".$this->incrementId."%"]);
            }
            if ($from && $to) {
                $orderCollection->addFieldToFilter("created_at", ["datetime"=>true, "from"=>$from, "to"=>$to]);
            }
            $orderCollection->setOrder("created_at", "desc");
            $orderList = [];
            if ($this->pageNumber >= 1) {
                $this->returnArray["totalCount"] = $orderCollection->getSize();
                $pageSize = $this->helperCatalog->getPageSize();
                $orderCollection->setPageSize($pageSize)->setCurPage($this->pageNumber);
            }
            foreach ($orderCollection as $res) {
                $order    = $this->dashboardHelper->getMainOrder($res["order_id"]);
                $status   = $order->getStatus();
                $name     = $order->getCustomerName();
                $tracking = $this->marketplaceOrderhelper->getOrderinfo($res["order_id"]);
                if (!is_array($tracking) && $tracking->getIsCanceled()) {
                    $status = "Canceled";
                }
                $eachOrder                 = [];
                $eachOrder["status"]       = strtoupper($status);
                $eachOrder['statusColorCode'] = $this->getOrderStatusColor($status);
                $eachOrder["orderId"]      = $res["order_id"];
                $eachOrder["incrementId"]  = $res["magerealorder_id"];
                $eachOrder["productNames"] = $this->dashboardHelper->getpronamebyorder($res["order_id"]);
                if ($this->marketplaceHelper->getSellerProfileDisplayFlag()) {
                    $eachOrder["customerDetails"]["name"] = $name;
                }
                $eachOrder["customerDetails"]["date"] = $this->viewTemplate->formatDate($res["created_at"]);
                $orderPrice = $this->dashboardHelper->getPricebyorder($res["order_id"]);
                $eachOrder["customerDetails"]["baseTotal"]     = $this->helperCatalog->stripTags(
                    $order->formatBasePrice($orderPrice)
                );
                $eachOrder["customerDetails"]["purchaseTotal"] = $this->helperCatalog->stripTags(
                    $order->formatPrice($this->dashboardHelper->getOrderedPricebyorder($order, $orderPrice))
                );
                $orderList[] = $eachOrder;
            }
            $this->returnArray["orderList"] = $orderList;
            $orderStatus = [];
            $statusColl  = $this->marketplaceOrderhelper->getOrderStatusData();
            foreach ($statusColl as $status) {
                $orderStatus[] = $status;
            }
            $this->returnArray["orderStatus"] = $orderStatus;
            $this->returnArray["manageOrder"] = (bool)$this->helper->getConfigData(
                "marketplace/general_settings/order_manage"
            );
            $this->returnArray["success"]     = true;
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
       * GetOrderStatusColor function
       *
       * @param [String] $status
       * @return String
       */
    public function getOrderStatusColor($status)
    {
        $status = strtolower($status);
        if ($status == 'new' ||
            $status == 'pending payment' ||
            $status == 'pending'
        ) {
            return '#ffbc00';
        } elseif ($status == 'processing') {
            return '#007EFF';
        } elseif ($status == 'complete') {
            return '#19A709';
        } elseif ($status == 'canceled' ||
            $status == 'closed' ||
            $status == 'fraud' ||
            $status == 'payment review'
        ) {
            return '#ff3b00';
        } else {
            return '#ffbc00';
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
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
            $this->dateTo        = $this->wholeData["dateTo"]        ?? "";
            $this->dateFrom      = $this->wholeData["dateFrom"]      ?? "";
            $this->status        = $this->wholeData["status"]        ?? "";
            $this->incrementId   = $this->wholeData["incrementId"]   ?? "";
            $this->pageNumber    = $this->wholeData["pageNumber"]    ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->customerId    = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            } elseif ($this->customerId != 0) {
                $this->customerSession->setCustomerId($this->customerId);
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    /**
     * Function to get Order order Ids Array
     *
     * @param int    $customerId        customer id of the order whose information is required
     * @param string $filterOrderstatus status of order for filter purpose
     *
     * @return array
     */
    public function getOrderIdsArray($customerId = "", $filterOrderstatus = "")
    {
        $orderids         = [];
        $collectionOrders = $this->orderCollectionFactory->create()
            ->addFieldToFilter("seller_id", $customerId)
            ->addFieldToSelect("order_id")
            ->distinct(true);
        foreach ($collectionOrders as $collectionOrder) {
            $tracking = $this->getOrderinfo($collectionOrder->getOrderId(), $customerId);
            if ($tracking) {
                if ($filterOrderstatus) {
                    if ($tracking->getIsCanceled()) {
                        if ($filterOrderstatus == "canceled") {
                            array_push($orderids, $collectionOrder->getOrderId());
                        }
                    } else {
                        $tracking = $this->orderRepository->get($collectionOrder->getOrderId());
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
     * Fucntion to get entity Ids
     *
     * @param array $orderids order id of the order whose information is required
     *
     * @return array
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
     * Fucntion o gte Order Info from orderId and customerId
     *
     * @param int $orderId    order id of the order whose information is required
     * @param int $customerId cusstomer id of the order whose information is required
     *
     * @return array
     */
    public function getOrderinfo($orderId = "", $customerId = "")
    {
        $data  = [];
        $model = $this->marketplaceOrders
            ->getCollection()
            ->addFieldToFilter("seller_id", $customerId)
            ->addFieldToFilter("order_id", $orderId);
        $salesOrder = $this->marketplaceOrderResourceCollection->getTable("sales_order");
        $model->getSelect()->join(
            $salesOrder." as so",
            "main_table.order_id=so.entity_id",
            ["order_approval_status"=>"order_approval_status"]
        )->where("so.order_approval_status=1");
        foreach ($model as $tracking) {
            $data = $tracking;
        }
        return $data;
    }
}
