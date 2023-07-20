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
 * Class CreditMemoList controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class CreditMemoList extends AbstractMarketplace
{

    /**
     * Execute function for class CreditMemoList
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->customerSession->setCustomerId($this->customerId);
            $order   = $this->order->loadByIncrementId($this->incrementId);
            $orderId = $order->getId();

            $this->returnArray['subHeading']          = __('Creditmemo List');
            $this->returnArray['mainHeading']         = __('View All Memos');
            $this->returnArray['statusHeading']       = __('Status');
            $this->returnArray['amountHeading']       = __('Amount');
            $this->returnArray['actionHeading']       = __('Action');
            $this->returnArray['createdAtHeading']    = __('Created At');
            $this->returnArray['creditMemoIdHeading'] = __('Credit Memos #');
            
            $creditMemoLists = [];
            $collection = $this->getMemoCollection($orderId);
            if ($this->pageNumber >= 1) {
                $returnArray["totalCount"] = $collection->getSize();
                $pageSize = $this->helperCatalog->getPageSize();
                $collection->setPageSize($pageSize)->setCurPage($this->pageNumber);
            }
            foreach ($collection as $creditmemo) {
                $oneMemoData = [];
                $oneMemoData['entityId'] = $creditmemo['entity_id'];
                $oneMemoData['incrementId'] = $creditmemo['increment_id'];
                $oneMemoData['billToName'] = $order->getCustomerName();
                $oneMemoData['createdAt'] = $creditmemo->getCreatedAt();
                $oneMemoData['status'] = __('Refunded');
                $oneMemoData['amount'] = $this->helperCatalog->stripTags(
                    $order->formatPrice($creditmemo->getGrandTotal())
                );
                $creditMemoLists[] = $oneMemoData;
            }
            $this->returnArray['creditMemoList'] = $creditMemoLists;
            $this->returnArray["success"]   = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
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
            $this->storeId       = $this->wholeData["storeId"]     ?? 0;
            $this->incrementId   = $this->wholeData["incrementId"] ?? 0;
            $this->pageNumber    = $this->wholeData["pageNumber"]  ?? 1;
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
     * Function to get Memo Collection
     *
     * @param int \Magento\Sales\Model\Order $orderId order
     *
     * @return bool|\Magento\Sales\Model\Order\Creditmemo\Collection
     */
    public function getMemoCollection($orderId)
    {
        $tracking = $this->marketplaceOrderhelper->getOrderinfo($orderId);
        $creditmemo = [];
        if ($tracking) {
            $creditmemoIds = [];
            $creditmemoIds = explode(',', $tracking->getCreditmemoId());
            $creditmemo = $this->creditmemo->getCollection()
                ->addFieldToFilter(
                    'entity_id',
                    ['in' => $creditmemoIds]
                );
        }
        return $creditmemo;
    }
}
