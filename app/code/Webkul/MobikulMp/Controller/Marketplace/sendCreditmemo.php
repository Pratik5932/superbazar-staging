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
 * Class SendCreditMemo for sending creditmemo to customer
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class SendCreditmemo extends AbstractMarketplace
{
    /**
     * Execute function for class SendCreditmemo
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment   = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->customerSession->setCustomerId($this->customerId);
            $order       = $this->order->loadByIncrementId($this->incrementId);
            $orderId = $order->getId();
            $memoDetails = $this->_initCreditmemo($this->creditmemoId, $order);
            $isPartner = $this->marketplaceHelper->isSeller();
            if ($isPartner && $memoDetails['success']) {
                $creditmemo = $memoDetails['creditmemo'];
                $this->creditmemoManager->notify($creditmemo->getEntityId());
                $this->returnArray["message"] = __('The message has been sent.');
                $this->returnArray["success"] = true;
            } else {
                $this->returnArray["message"] = $memoDetails['message'] ?? __('Failed to send the creditmemo email.');
                $this->returnArray["success"] = false;
            }
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->getJsonResponse(
                $this->returnArray
            );
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
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
            $this->incrementId   = $this->wholeData["incrementId"]   ?? 0;
            $this->creditmemoId  = $this->wholeData["creditmemoId"]  ?? 0;
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
     * Initialize invoice model instance.
     *
     * @param int   $creditmemoId creditmemo Id
     * @param order $order        order
     *
     * @return \Magento\Sales\Api\InvoiceRepositoryInterface|false
     */
    protected function _initCreditmemo($creditmemoId, $order)
    {
        $data = [];
        $data['success'] = false;
        $creditmemo = false;
        $creditmemo = $this->creditmemoRepository->get($creditmemoId);
        if (!$creditmemo) {
            return $data;
        }
        try {
            $tracking = $this->marketplaceOrderhelper->getOrderinfo($order->getId());
            if (count($tracking)) {
                $creditmemoArr = explode(',', $tracking->getCreditmemoId());
                if (in_array($creditmemoId, $creditmemoArr)) {
                    if (!$creditmemoId) {
                        $data['message'] = __("The creditmemo no longer exists.");
                        throw new \BadMethodCallException($data['message']);
                    }
                } else {
                    $data['message'] = __("You are not authorize to view this creditmemo.");
                    throw new \BadMethodCallException($data['message']);
                }
            } else {
                $data['message'] = __("You are not authorize to view this creditmemo.");
                throw new \BadMethodCallException($data['message']);
            }
        } catch (\NoSuchEntityException $e) {
            $data['message'] = $e->getMessage();
            throw new \BadMethodCallException($data['message']);
        } catch (\InputException $e) {
            $data['message'] = $e->getMessage();
            throw new \BadMethodCallException($data['message']);
        }
        $this->coreRegistry->register('sales_order', $order);
        $this->coreRegistry->register('current_order', $order);
        $this->coreRegistry->register('current_creditmemo', $creditmemo);
        $data['success'] = true;
        $data['creditmemo'] = $creditmemo;
        return $data;
    }
}
