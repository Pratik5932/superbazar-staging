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
 * Class UpdateCart
 * To update cart
 */
class UpdateCart extends AbstractCheckout
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $websiteId = $this->storeManager->getStore($this->storeId)->getWebsiteId();
            $quote = new \Magento\Framework\DataObject();
            if ($this->customerId != 0) {
                $quote = $this->helper->getCustomerQuote($this->customerId);
            }
            if ($this->quoteId != 0) {
                $quote = $this->quoteFactory->create()->setStoreId($this->storeId)->load($this->quoteId);
            }
            $cartData = [];
            #echo "dasd";exit;
            foreach ($this->itemIds as $key => $itemId) {
                $cartData[$itemId] = ["qty"=>$this->itemQtys[$key]];
            }
            $filter = new \Magento\Framework\Filter\LocalizedToNormalized(
                ["locale"=>$this->localeResolver->getLocale()]
            );
            foreach ($cartData as $index => $eachData) {
                if (isset($eachData["qty"])) {
                    $cartData[$index]["qty"] = $filter->filter(trim($eachData["qty"]));
                }
            }
            foreach ($cartData as $itemId => $itemInfo) {
                if (!isset($itemInfo["qty"])) {
                    continue;
                }
                $qty = (float) $itemInfo["qty"];
                $quoteItem = $quote->getItemById($itemId);
                if (!$quoteItem) {
                    continue;
                }
                $product = $quoteItem->getProduct();
                if (!$product) {
                    continue;
                }
                if ($product->getTypeId() == "configurable") {
                    $options = $product->getTypeInstance(true)->getOrderOptions($product);
                    $superAttributes = [];
                    foreach ($options['attributes_info'] as $option) {
                        $superAttributes[$option['option_id']] = $option['option_value'];
                    }
                    $configurable = $this->_objectManager->create(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::class);
                    $childProduct = $configurable->getProductByAttributes(
                        $superAttributes,
                        $product
                    );
                    $stockData = $this->stockRegistry->getStockItem($childProduct->getId());
                    $availableQty = $stockData->getQty();    
                    if ($qty <= $stockData->getBackorders()) {
                        $this->returnArray['message'] = __("Your requested quantity is not available of this product.");
                        $this->returnArray['success'] = false;
                        return $this->getJsonResponse($this->returnArray);
                    }
                }
                $stockItem = $this->stockRegistry->getStockItem($product->getId());
                $avlQty = $this->stockRegistry->getStockItem($product->getId())->getQty();
                if ($product->getTypeId() != "configurable" && $avlQty < $qty &&  !$stockItem->getBackorders()) {
                    $this->returnArray['message'] = __("Your requested quantity is not available of this product.");
                    $this->returnArray['success'] = false;
                    return $this->getJsonResponse($this->returnArray);
                }
                if (!$stockItem) {
                    continue;
                }
                $quoteItem->setQty($qty)->save();
            }
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->collectTotals()->save();
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Cart updated successfully");
            $this->returnArray["cartCount"] = $quote->getItemsQty()*1;
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Function to verify request
     *
     * @return void|json
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->itemIds = $this->wholeData["itemIds"] ?? "[]";
            $this->itemQtys = $this->wholeData["itemQtys"] ?? "[]";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            $this->itemIds = $this->jsonHelper->jsonDecode($this->itemIds);
            $this->itemQtys = $this->jsonHelper->jsonDecode($this->itemQtys);
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
