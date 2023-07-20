<?php
namespace Webkul\MobikulMpHyperLocal\Controller\Checkout;

class AddToCart extends \Webkul\MobikulApi\Controller\Checkout\AddToCart
{
    public function execute()
    {
        $data = $this->verifyRequest();
        if ($data) {
            $this->returnArray["message"] = $data;
            $this->returnArray["success"] = false;
            return $this->getJsonResponse($this->returnArray);
        }
        try {
            if ($this->wholeData) {
                $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
                if ($this->helper->getConfigData("sales/minimum_order/active")) {
                    $this->returnArray["minimumAmount"] = (int)$this->helper->getConfigData("sales/minimum_order/amount");
                    $this->returnArray["minimumFormattedAmount"] = $this->helperCatalog->stripTags(
                        $this->checkoutHelper->formatPrice($this->returnArray["minimumAmount"])
                    );
                } else {
                    $this->returnArray["minimumAmount"] = 0;
                    $this->returnArray["minimumFormattedAmount"] = $this->helperCatalog->stripTags(
                        $this->checkoutHelper->formatPrice(0)
                    );
                }
                $quote = new \Magento\Framework\DataObject();
                // added check for expired quote ////////////////////////////////////
                if ($this->quoteId) {
                    $size = $this->quoteFactory
                        ->create()
                        ->getCollection()
                        ->addFieldToFilter("entity_id", ["eq" => $this->quoteId])
                        ->getSize();
                    if (!$size) {
                        $this->quoteId = 0;
                    }
                }
                // end added check for expired quote ////////////////////////////////
                if ($this->customerId == 0 && $this->quoteId == 0) {
                    $this->setQuoteIdData();
                }
                if ($this->qty == 0) {
                    $this->qty = 1;
                }
                if ($this->customerId != 0) {
                    $this->saveQuoteCustomerData();
                }
                $quote = $this->helper->getQuoteById($this->quoteId)->setStoreId($this->storeId);
                $product = $this->productFactory->create()->setStoreId($this->storeId)->load($this->productId);
                if ($this->qty && !($product->getTypeId() == "downloadable")) {
                    $this->checkStockData($product,$quote);
                }

                $request = [];
                $paramOption = [];
                $filesToDelete = [];
                // if (isset($this->params["options"])) {
                $request = $this->setProductParamOptions($product);
                // }
                $request["qty"] = $this->qty;
                $request = $this->_getProductRequest($request);
                $productAdded = $quote->addProduct($product, $request);
                $result = $this->checkRelatedProduct($quote);
                $allAdded = $result['allAdded'];
                $allAvailable = $result['allAvailable'];
                $quote->collectTotals()->save();
                if (!$productAdded || is_string($productAdded)) {
                    $this->returnArray["message"] = __("Unable to add product to cart.");
                    if (is_string($productAdded)) {
                        $this->returnArray["message"] = $productAdded;
                    }
                    return $this->getJsonResponse($this->returnArray);
                } else {
                    $this->returnArray["cartCount"] = $this->helper->getCartCount($quote);
                }
                $this->returnArray["message"] = htmlspecialchars_decode(
                    __("You added %1 to your shopping cart.", $this->helperCatalog->stripTags($product->getName()))
                );
                if (!$allAvailable) {
                    $this->returnArray["message"] .= __(" but, We don't have some of the products you want.");
                }
                if (!$allAdded) {
                    $this->returnArray["message"] .= __(" but, We don't have as many of some products as you want.");
                }
                // delete files uploaded for custom option /////////////////
                foreach ($filesToDelete as $eachFile) {
                    $this->fileDriver->deleteFile($eachFile);
                }
                // validate minimum amount check ////////////////////////////////////
                $isCheckoutAllowed = $quote->validateMinimumAmount();
                if (!$isCheckoutAllowed) {
                    $this->returnArray["isCheckoutAllowed"] = false;
                    $this->returnArray["descriptionMessage"] = $this->helper->getConfigData(
                        "sales/minimum_order/description"
                    );
                } elseif ($quote->getHasError()) {
                    $this->returnArray["isCheckoutAllowed"] = false;
                } else {
                    $this->returnArray["isCheckoutAllowed"] = true;
                }
                $this->returnArray["cartTotal"] = $quote->getGrandTotal();
                $this->returnArray["cartTotalFormattedAmount"] = $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($quote->getGrandTotal())
                );
                $this->returnArray["isVirtual"] = (bool)$quote->getIsVirtual();
                $this->returnArray["success"] = true;
                $this->emulate->stopEnvironmentEmulation($environment);
                $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
                return $this->getJsonResponse($this->returnArray);
            } else {
                $this->returnArray["message"] = __("Invalid Request");
                $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
                return $this->getJsonResponse($this->returnArray);
            }
        } catch (\Exception $e) {
            if ($e->getMessage() != "") {
                $this->returnArray["message"] = $e->getMessage();
            } else {
                $this->returnArray["message"] = __("Can't add the item to shopping cart.");
            }
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage() != "") {
                $this->returnArray["message"] = $e->getMessage();
            } else {
                $this->returnArray["message"] = __("Can't add the item to shopping cart.");
            }
            $this->_helper->printLog($returnArray, 1);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->qty = $this->wholeData["qty"] ?? 1;
            $this->params = $this->wholeData["params"] ?? "{}";
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->productId = $this->wholeData["productId"] ?? 0;
            $this->relatedProducts = $this->wholeData["relatedProducts"] ?? "[]";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->params = $this->jsonHelper->jsonDecode($this->params);
            $this->relatedProducts = $this->jsonHelper->jsonDecode($this->relatedProducts);
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            }
            if(isset($this->wholeData['address']) && isset($this->wholeData['latitude']) && isset($this->wholeData['longitude'])) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $hyperlocalHelper = $objectManager->create(\Webkul\MpHyperLocal\Helper\Data::class);
                $sellerIds = $hyperlocalHelper->getNearestSellers();
                $allowedProList = $hyperlocalHelper->getNearestProducts($sellerIds);
                if (!in_array($this->productId,$allowedProList)) {
                    // throw new \Magento\Framework\Exception\LocalizedException(
                        return $datas["message"] = __("Product unavailable in selected region.");
                    // );
                }
            }
        } else {
            throw new \Exception(__("Invalid Request"));
        }
    }
}