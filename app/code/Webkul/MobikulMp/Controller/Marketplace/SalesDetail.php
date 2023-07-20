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
 * Class SalesDetails controller
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
class SalesDetail extends AbstractMarketplace
{
    /**
     * Execute function for class AskQuestionToAdmin
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "SALESDETAIL".$this->storeId.$this->productId.$this->customerToken.$this->customerId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $customer = $this->customer->load($this->customerId);
            $this->customerSession->setCustomer($customer);
            $this->customerSession->setCustomerId($this->customerId);
            $collectionOrders = $this->marketplaceSaleList
                ->getCollection()
                ->addFieldToFilter("seller_id", $this->customerId)
                ->addFieldToFilter("mageproduct_id", $this->productId)
                ->addFieldToFilter("magequantity", ["neq"=>0])
                ->addFieldToSelect("order_id")
                ->distinct(true);
            $collection = $this->marketplaceOrders
                ->getCollection()
                ->addFieldToFilter("order_id", ["in"=>$collectionOrders->getData()])
                ->setOrder("entity_id", "desc");
            $salesList = [];
            foreach ($collection as $marketplaceOrder) {
                $eachSale   = [];
                $orderId    = $marketplaceOrder->getOrderId();
                $order      = $this->order->load($orderId);
                $shipmentId = 0;
                $invoiceId  = 0;
                $shipmentId = $marketplaceOrder->getShipmentId();
                $invoiceId  = $marketplaceOrder->getInvoiceId();
                $eachSale["orderId"]     = $orderId;
                $eachSale["buyerName"]   = "";
                $eachSale["incrementId"] = $order["increment_id"];
                if ($this->_marketplaceHelper->getSellerProfileDisplayFlag()) {
                    $eachSale["buyerName"] = $order->getCustomerName();
                }
                $eachSale["date"]          = $this->viewTemplate->formatDate($marketplaceOrder->getCreatedAt());
                $eachSale["invoiceId"]     = $invoiceId;
                $eachSale["shipmentId"]    = $shipmentId;
                $salesList[] = $eachSale;
            }
            $this->returnArray["salesList"] = $salesList;
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
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
            $this->productId     = $this->wholeData["productId"]     ?? 0;
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
}
