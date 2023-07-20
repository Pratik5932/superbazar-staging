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
 * Class TransactionList controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class TransactionList extends AbstractMarketplace
{
    /**
     * Execute function for class TransactionList
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "TRANSACTIONLIST".$this->storeId.$this->dateFrom.$this->dateTo.$this->pageNumber;
            $cacheString .= $this->transactionId.$this->customerToken.$this->customerId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment   = $this->emulate->startEnvironmentEmulation($this->storeId);

            $this->getTransactionWithdrawalData();

            $transactionCollection = $this->transactionCollectionFactory->create()
                ->addFieldToSelect("*")
                ->addFieldToFilter("seller_id", $this->customerId);
            $to   = null;
            $from = null;
            if ($this->dateTo) {
                $todate = date_create($this->dateTo);
                $to = date_format($todate, "Y-m-d 23:59:59");
            }
            if ($this->dateFrom) {
                $fromdate = date_create($this->dateFrom);
                $from = date_format($fromdate, "Y-m-d H:i:s");
            }
            if ($this->transactionId) {
                $transactionCollection->addFieldToFilter("transaction_id", ["like"=>"%".$this->transactionId."%"]);
            }
            if ($from && $to) {
                $transactionCollection->addFieldToFilter("created_at", ["datetime"=>true, "from"=>$from, "to"=>$to]);
            }
            $transactionCollection->setOrder("created_at", "desc");
            if ($this->pageNumber >= 1) {
                $this->returnArray["totalCount"] = $transactionCollection->getSize();
                $pageSize = $this->helperCatalog->getPageSize();
                $transactionCollection->setPageSize($pageSize)->setCurPage($this->pageNumber);
            }
            $transactionList = [];
            foreach ($transactionCollection as $transaction) {
                $eachTransaction                  = [];
                $eachTransaction["id"]            = $transaction->getId();
                $eachTransaction["date"]          = $this->viewTemplate->formatDate($transaction->getCreatedAt());
                $eachTransaction["amount"]        = $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($transaction->getTransactionAmount())
                );
                $eachTransaction["comment"]       = __("None");
                $eachTransaction["transactionId"] = $transaction->getTransactionId();
                if ($transaction->getCustomNote()) {
                    $eachTransaction["comment"]   = $transaction->getCustomNote();
                }
                $transactionList[]                = $eachTransaction;
            }
            $this->returnArray["transactionList"] = $transactionList;
            $collection = $this->saleperPartner->getCollection()->addFieldToFilter("seller_id", $this->customerId);
            $total = 0;
            foreach ($collection as $key) {
                $total = $key->getAmountRemain();
            }
            if ($total < 0) {
                $total = 0;
            }
            $this->returnArray["remainingTransactionAmount"] = $this->helperCatalog->stripTags(
                $this->checkoutHelper->formatPrice($total)
            );
            $this->returnArray["success"] = true;
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

    private function getTransactionWithdrawalData()
    {
        $transactionBlock = $this->transactionWithdrawalBlock;
        $this->returnArray["totalSellerEarning"] = $transactionBlock->getFormatedPrice(
            $transactionBlock->getTotalSellerSale()
        );
        $this->returnArray["unformattedTotalSellerEarning"] = (float) $transactionBlock->getTotalSellerSale();
        $this->returnArray["totalSale"] = $transactionBlock->getFormatedPrice($transactionBlock->getTotalSale());
        $this->returnArray["unformattedTotalSale"] = (float) $transactionBlock->getTotalSale();
        $this->returnArray["totalTax"] = $transactionBlock->getFormatedPrice($transactionBlock->getTotalTax());
        $this->returnArray["unformattedTotalTax"] = (float) $transactionBlock->getTotalTax();
        $this->returnArray["totalCommission"] = $transactionBlock->getFormatedPrice(
            $transactionBlock->getTotalCommission()
        );
        $this->returnArray["unformattedTotalCommission"] = (float) $transactionBlock->getTotalCommission();
        $this->returnArray["totalPayout"] = $transactionBlock->getFormatedPrice($transactionBlock->getTotalPayout());
        $this->returnArray["unformattedTotalPayout"] = (float) $transactionBlock->getTotalPayout();
        $this->returnArray["remainingPayout"] = $transactionBlock->getFormatedPrice(
            $transactionBlock->getRemainTotal()
        );
        $this->returnArray["unformattedRemainingPayout"] = (float) $transactionBlock->getRemainTotal();
        $this->returnArray["isWithdrawalEligible"] = 0;
        if ($transactionBlock->getRemainTotal()*1) {
            $this->returnArray["isWithdrawalEligible"] = 1;
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
            $this->dateFrom      = $this->wholeData["dateFrom"]      ?? "";
            $this->dateTo        = $this->wholeData["dateTo"]        ?? "";
            $this->pageNumber    = $this->wholeData["pageNumber"]    ?? 1;
            $this->transactionId = $this->wholeData["transactionId"] ?? "";
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
