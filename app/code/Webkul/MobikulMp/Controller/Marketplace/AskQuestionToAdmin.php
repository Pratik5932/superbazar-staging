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

class AskQuestionToAdmin extends AbstractMarketplace
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
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $customer = $this->customer->load($this->customerId);
            $adminEmail = $this->marketplaceHelper->getAdminEmailId();
            $adminEmail = $adminEmail ? $adminEmail : $this->marketplaceHelper->getDefaultTransEmailId();
            $sellerName = $customer->getName();
            $senderInfo = [];
            $sellerEmail = $customer->getEmail();
            $templateVars = [];
            $receiverInfo = [];
            $adminUsername = "Admin";
            $templateVars["myvar1"]  = $adminUsername;
            $templateVars["myvar2"]  = $sellerName;
            $templateVars["myvar3"]  = $this->query;
            $templateVars["subject"] = $this->subject;
            $senderInfo = [
                "name"  => $sellerName,
                "email" => $sellerEmail
            ];
            $receiverInfo = [
                "name"  => $adminUsername,
                "email" => $adminEmail
            ];
            $this->marketplaceEmailHelper->askQueryAdminEmail($templateVars, $senderInfo, $receiverInfo);
            $this->returnArray["success"] = true;
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
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->query         = $this->wholeData["query"]         ?? "";
            $this->subject       = $this->wholeData["subject"]       ?? "";
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
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
