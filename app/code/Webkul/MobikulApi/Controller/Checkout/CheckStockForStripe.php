<?php
/**
* Webkul Software.
*
* @category  Webkul
* @package   Webkul_MobikulApi
* @author    Webkul <support@webkul.com>
* @copyright Webkul Software Private Limited (https://webkul.com)
* @license   https://store.webkul.com/license.html ASL Licence
* @link      https://store.webkul.com/license.html
*/

namespace Webkul\MobikulApi\Controller\Checkout;

/**
* Class CheckStockForStripe
* To get order review data and available payment methods
*/
class CheckStockForStripe extends AbstractCheckout
{
    public function execute()
    {
        try {
            $productRepository = $this->_objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
            $stockItemRepository = $this->_objectManager->create(\Magento\CatalogInventory\Api\StockStateInterface::class);
            $productRepoInterface = $this->_objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
            $backOrderOptions = $this->_objectManager->create(\Magento\CatalogInventory\Model\Source\Backorders::class);
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $quote = new \Magento\Framework\DataObject();
            if ($this->quoteId != 0) {
                $quote = $this->quoteFactory->create()->setStoreId($this->storeId)->load($this->quoteId);
                foreach ($quote->getAllVisibleItems() as $item) {
                    $product = $item->getProduct();
                    $product = $productRepository->getById($product->getId());
                    $backOrderProduct = $productRepoInterface->getById($product->getId());
                    $data = $backOrderProduct->getExtensionAttributes()->getStockItem()->getBackorders();
                    $stockData = $stockItemRepository->getStockQty($product->getId(), $this->storeId);
                    if (!$product->isAvailable() || ($stockData < $item['qty'] && $data != 2)) {
                        $this->returnArray['message'] = __("Some of the products are out of stock.");
                        $this->returnArray['success'] = false;
                        return $this->getJsonResponse($this->returnArray);
                    }
                }
                $this->returnArray["success"] = true;
                $this->returnArray["message"] = "";
            } else {
                $this->returnArray["success"] = false;
                $this->returnArray["message"] = __("Please provide the quote id.");
            }
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"] = $e->getMessage();
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse(
                $this->returnArray
            );
        } catch (\Exception $e) {
            $this->returnArray["message"] = $e->getMessage();
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
    * Function to veriy the Request
    *
    * @return void|object
    */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
