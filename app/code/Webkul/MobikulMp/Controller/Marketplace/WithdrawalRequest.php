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
 * Class WithdrawalRequest controller
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class WithdrawalRequest extends AbstractMarketplace
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
            $cacheString = "TRANSACTIONLIST".$this->storeId.$this->customerToken.$this->isRequested.$this->customerId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment   = $this->emulate->startEnvironmentEmulation($this->storeId);

            $helper = $this->marketplaceHelper;
            $isPartner = $helper->isSeller();
            if ($isPartner == 1) {
                if ($this->isRequested == '1') {
                    $sellerId = $this->customerId;
                    $collection = $this->orderCollectionFactory->create();
                    
                    $coditionArr = [];
                    $condition = "`seller_id`=".$sellerId;
                    array_push($coditionArr, $condition);
                    $condition = "`cpprostatus`=1";
                    array_push($coditionArr, $condition);
                    $condition = "`paid_status`=0";
                    array_push($coditionArr, $condition);
                    $coditionData = implode(' AND ', $coditionArr);

                    $collection->setWithdrawalRequestData(
                        $coditionData,
                        ['is_withdrawal_requested' => 1]
                    );

                    $adminStoreEmail = $helper->getAdminEmailId();
                    $adminEmail = $adminStoreEmail ? $adminStoreEmail : $helper->getDefaultTransEmailId();
                    $adminUsername = 'Admin';

                    $seller = $this->customerRepository->getById(
                        $sellerId
                    );

                    $emailTemplateVariables = [];
                    $emailTemplateVariables['seller'] = $seller->getFirstName();
                    $emailTemplateVariables['amount'] = $helper->getFormatedPrice(
                        $this->getRemainTotal()
                    );

                    $receiverInfo = [
                        'name' => $adminUsername,
                        'email' => $adminEmail,
                    ];
                    $senderInfo = [
                        'name' => $seller->getFirstName(),
                        'email' => $seller->getEmail(),
                    ];
                    $this->marketplaceEmailHelper->sendWithdrawalRequestMail(
                        $emailTemplateVariables,
                        $senderInfo,
                        $receiverInfo
                    );
                    $this->returnArray["message"] = __('Your withdrawal request has been sent successfully.');
                    $this->returnArray["success"] = true;
                }
            } else {
                $this->returnArray["message"] = __('Seller you are requesting does not exist.');
            }
            
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
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->isRequested   = $this->wholeData["is_requested"] ?? 0;
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
     * @return int|float
     */
    public function getRemainTotal()
    {
        $sellerId = $this->customerId;
        $collection = $this->partnerCollectionFactory->create()
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        );
        $total = 0;
        foreach ($collection->getTotalAmountRemain() as $data) {
            $total = $data['amount_remain'];
        }
        return $total;
    }
}
