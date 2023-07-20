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
* Class CartDetails
* To return the details about item and totals in cart.
*/
class CartDetails extends AbstractCheckout
{
    /**
    * Execute function for class CartDetails
    *
    * @return json|void
    */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            // Setting currency /////////////////////////////////////////////////////
            $this->store->setCurrentCurrencyCode($this->currency);
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

            $this->returnArray["customPrice"] = 0;
            $this->returnArray["showThreshold"] = (bool)$this->helper->getConfigData(
                "cataloginventory/options/product_stock_status"
            );
            $this->quote = new \Magento\Framework\DataObject();
            if ($this->customerId != 0) {
                $this->quote = $this->helper->getCustomerQuote($this->customerId);
            }
            if ($this->quoteId != 0) {
                $this->quote = $this->helper->getQuoteById($this->quoteId)->setStoreId($this->storeId);
            }
            $this->quote->collectTotals()->save();
            $this->returnArray["cartId"] = $this->quote->getId();
            $this->returnArray["canGuestCheckoutDownloadable"] = false;
            if ($this->helper->getConfigData("catalog/downloadable/disable_guest_checkout") == 0) {
                $this->returnArray["canGuestCheckoutDownloadable"] = true;
            }
            $this->returnArray["allowMultipleShipping"] = (bool)$this->helper->getConfigData(
                "multishipping/options/checkout_multiple"
            );
            if ($this->customerId != 0 || $this->quoteId != 0) {
                $this->customPrice($this->quote->getId());
                $this->quote->getShippingAddress()->setCollectShippingRates(true);
                $this->quote->collectTotals()->save();
                // getting cart items ///////////////////////////////////////////////
                $cartItemData = $this->getCartItemList();
                #  echo($cartItemData["totalCount"]);exit;
                $this->returnArray["items"] = $cartItemData["items"];
                $this->returnArray["totalCount"] = count($cartItemData["items"]);
                if ($this->quote->getCouponCode() !== null) {
                    $this->returnArray["couponCode"] = $this->quote->getCouponCode();
                }
                if ($this->quote->getIsVirtual()) {
                    $this->returnArray["isVirtual"] = (bool)$this->quote->getIsVirtual();
                }
                // getting cross sell list //////////////////////////////////////////
                $this->returnArray["crossSellList"] = $this->getCrossSellList();
                if ($this->checkoutHelper->isAllowedGuestCheckout($this->quote)) {
                    $this->returnArray["isAllowedGuestCheckout"] = $this->checkoutHelper->isAllowedGuestCheckout(
                        $this->quote
                    );
                }
                $this->returnArray["cartCount"] = count($cartItemData["items"]);
                // getting totals details ///////////////////////////////////////////
                if ($this->returnArray["cartCount"] > 0) {
                    foreach ($this->getTotalsData() as $key => $eachTotal) {
                        $this->returnArray['totalsData'][] = $eachTotal;
                    }
                }
                // validate minimum amount check ////////////////////////////////////
                $isCheckoutAllowed = $this->quote->validateMinimumAmount();
                if (!$isCheckoutAllowed) {
                    $this->returnArray["isCheckoutAllowed"] = false;
                    $this->returnArray["descriptionMessage"] = $this->helper->getConfigData(
                        "sales/minimum_order/description"
                    );
                } elseif ($this->quote->getHasError()) {
                    $this->returnArray["isCheckoutAllowed"] = false;
                } else {
                    $this->returnArray["isCheckoutAllowed"] = true;
                }
            }
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    public function customPrice($quote_id)
    {
        $deduct_price = 0;
        $quoteItem = '';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $context = $objectManager->get('Magento\Framework\App\Http\Context');
        $isLoggedIn = $context->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        $itemsCollection = $cart->getQuote()->getItemsCollection()->addFieldToFilter('quote_id', $quote_id);
        $zipCode = $_GET['address'];
        foreach($itemsCollection->getData() as $item) 
        {
            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item['product_id']);

            $orgprice = $product->getPrice();
            $specialprice = $product->getSpecialPrice();
            $specialfromdate = $product->getSpecialFromDate();
            $specialtodate = $product->getSpecialToDate();
            $today = time();
            $specialPriceflag = false;
            if (!$specialprice)
                $specialprice = $orgprice;
            if ($specialprice< $orgprice) {
                if ((is_null($specialfromdate) &&is_null($specialtodate)) || ($today >= strtotime($specialfromdate) &&is_null($specialtodate)) || ($today <= strtotime($specialtodate) &&is_null($specialfromdate)) || ($today >= strtotime($specialfromdate) && $today <= strtotime($specialtodate))) {
                    $specialprice = $specialprice;
                    $specialPriceflag =true;
                }
            }


            if ($product->getPostcodeProdctPrice() && $zipCode) {
                $postcodeProdctPriceArray = json_decode($product->getPostcodeProdctPrice(),true);
                if($postcodeProdctPriceArray && is_array($postcodeProdctPriceArray) && !empty($postcodeProdctPriceArray)){
                    $zipCode = $_GET['address'];
                    $zipCodePrice = null;
                    foreach($postcodeProdctPriceArray as $value){

                        $postCodesValueArray = [];

                        if(isset($value['postcode']) && $value['postcode']){
                            $postCodesValueArray = array_map('trim', explode(',', $value['postcode']));
                        }

                        if($value && in_array($zipCode, $postCodesValueArray) && isset($value['price'])){
                            $zipCodePrice = $value['price'];
                        }
                    }

                    if($zipCodePrice != null && !$specialPriceflag){
                        $deduct_price  = $item['price'] - $zipCodePrice;
                        $quoteRepository = $objectManager->create('\Magento\Quote\Api\CartRepositoryInterface');
                        $quote = $quoteRepository->getActive($quote_id);
                        $quoteItem = $quote->getItemById($item['item_id']);
                        if (!$quoteItem) {
                            continue;
                        }
                        $quoteItem->setData('custom_price', $zipCodePrice);
                        $quoteItem->setData('original_custom_price', $zipCodePrice);
                        $quoteItem->getProduct()->setIsSuperMode(true);
                        $quoteItem->save();
                    }else{
                        $deduct_price  =$specialprice;
                        $quoteRepository = $objectManager->create('\Magento\Quote\Api\CartRepositoryInterface');
                        $quote = $quoteRepository->getActive($quote_id);
                        $quoteItem = $quote->getItemById($item['item_id']);
                        if (!$quoteItem) {
                            continue;
                        }
                        $quoteItem->setData('custom_price', $specialprice);
                        $quoteItem->setData('original_custom_price', $specialprice);
                        $quoteItem->getProduct()->setIsSuperMode(true);
                        $quoteItem->save();

                    }
                }
            }
        }
        return $quoteItem;
    }

    /**
    * Function to verify request
    *
    * @return json|void
    */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->currency = $this->wholeData["currency"] ?? $this->store->getBaseCurrencyCode();
            $this->pageNumber = $this->wholeData["pageNumber"] ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
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

    /**
    * Function to get cart Itme List
    *
    * @return array
    */
    protected function getCartItemList()
    {
        $productRepository = $this->_objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
        $this->items = [];
        $cartItemData = [];
        $this->cartProductIds[] = "";
        $zipCode = $_GET['address'];
        $deduct_price = 0;
        $deduct_price2 = 0;
        $total_deduct_price = 0;
        $itemCollection = $this->itemCollectionFactory->create()->setQuote($this->quote);
        $itemCollection->addFieldToFilter("parent_item_id", ["null" => true]);
        $cartItemData["totalCount"] = $itemCollection->getSize();
        // if ($this->pageNumber >= 1) {
        $cartItemData["totalCount"] = $itemCollection->getSize();
        //     $pageSize = $this->helper->getConfigData("checkout/cart/number_items_to_display_pager");
        //     $itemCollection->setPageSize($pageSize)->setCurPage($this->pageNumber);
        // }
        foreach ($itemCollection as $item) {
            $product = $item->getProduct();
            if ($product) {
                $this->cartProductIds[] = $product->getId();
            }
            $eachItem = [];
            $eachItem["image"] = $this->helperCatalog->getImageUrl(
                $product,
                $this->width/2.5,
                "product_page_image_small"
            );
            $eachItem["thresholdQty"] = $this->helper->getConfigData("cataloginventory/options/stock_threshold_qty");
            $eachItem["remainingQty"] = $this->stockState->getStockQty(
                $product->getId(),
                $product->getStore()->getWebsiteId()
            );
            $eachItem["dominantColor"] = $this->helper->getDominantColor(
                $this->helper->getDominantColorFilePath(
                    $this->helperCatalog->getImageUrl($product, $this->width/2.5, "product_page_image_small")
                )
            );
            $eachItem["name"] = htmlspecialchars_decode($item->getName());
            $eachItem["canMoveToWishlist"] = $item->getProduct()->isVisibleInSiteVisibility();
            $options = $product->getTypeInstance(true)->getOrderOptions($product);
            if ($product->getTypeId() == "configurable") {
                $skuProduct = $this->productFactory->create()
                ->getCollection()
                ->addFieldToSelect("entity_id")
                ->addStoreFilter($this->storeId)
                ->addFieldToFilter("sku", $item->getSku())
                ->getFirstItem();

                $skuProduct = $this->productFactory->create()->load($skuProduct["entity_id"]);
                $eachItem["image"] =$this->helperCatalog->getImageUrl(
                    $skuProduct,
                    $this->width/2.5,
                    "product_page_image_small"
                );

                $configurableOptions = $options["attributes_info"];
                foreach ($configurableOptions as $configurableOption) {
                    $eachConfigurableOption = [];
                    $eachConfigurableOption["optionId"] = $configurableOption["option_id"];
                    $eachConfigurableOption["label"] = $configurableOption["label"];
                    $eachConfigurableOption["valueIds"][] =htmlspecialchars_decode($configurableOption["option_value"]);
                    $eachConfigurableOption["value"][] = htmlspecialchars_decode($configurableOption["value"]);
                    $eachItem["options"][] = $eachConfigurableOption;
                }
            }
            if ($product->getTypeId() == "bundle") {
                $bundleOptions = $options["bundle_options"];
                foreach ($bundleOptions as $bundleOption) {
                    $eachBundleOption = [];
                    $eachBundleOption["optionId"] = $bundleOption["option_id"];
                    $eachBundleOption["label"] = $bundleOption["label"];
                    foreach ($bundleOption["value"] as $bundleOptionValue) {
                        $price = 0;
                        if ($bundleOptionValue["price"] > 0) {
                            $price = $bundleOptionValue["price"]/$bundleOptionValue["qty"];
                        }
                        $price = $this->helperCatalog->stripTags($this->priceHelper->currency($price));
                        $eachBundleOptionValue = $bundleOptionValue["qty"]." x ".$bundleOptionValue["title"]." ".$price;
                        $eachBundleOption["valueIds"] = ["qty"=>$bundleOptionValue["qty"]];
                        $eachBundleOption["value"][] = htmlspecialchars_decode($eachBundleOptionValue);
                    }
                    $eachItem["options"][] = $eachBundleOption;
                }
            }
            if ($product->getTypeId() == "downloadable") {
                $links = $this->downloadableConfiguration->getLinks($item);
                if (count($links) > 0) {
                    $downloadOption = [];
                    $titles = [];
                    foreach ($links as $linkId) {
                        $titles[] = htmlspecialchars_decode($linkId->getTitle(), ENT_QUOTES);
                    }
                    $downloadOption["label"] = $this->downloadableConfiguration->getLinksTitle($product);
                    $downloadOption["value"] = $titles;
                    $eachItem["options"][] = $downloadOption;
                }
            }
            if (isset($options["options"])) {
                $customOptions = $options["options"];
                foreach ($customOptions as $customOption) {
                    $eachCustomOption = [];
                    $eachCustomOption["label"] = $customOption["label"];
                    $eachCustomOption["value"][] = htmlspecialchars_decode($customOption["print_value"]);
                    $eachItem["options"][] = $eachCustomOption;
                }
            }
            $product->load($product->getId());

            $orgprice = $product->getPrice();
            $specialprice = $product->getSpecialPrice();
            $specialfromdate =$product->getSpecialFromDate();
            $specialtodate = $product->getSpecialToDate();
            $today = time();
            $specialPriceflag = false;
            if (!$specialprice)
                $specialprice = $orgprice;
            if ($specialprice < $orgprice) {
                if ((is_null($specialfromdate) &&is_null($specialtodate)) || ($today >= strtotime($specialfromdate) &&is_null($specialtodate)) || ($today <= strtotime($specialtodate) &&is_null($specialfromdate)) || ($today >= strtotime($specialfromdate) && $today <= strtotime($specialtodate))) {
                    $specialprice = $specialprice;
                    $specialPriceflag =true;
                }
            }
		#mail("er.bharatmali@gmail.com","specialprice", $specialprice);
		$product->load($product->getId());
 $zipCodePrice = null;
            if ($product->getPostcodeProdctPrice() && $zipCode) {
                $postcodeProdctPriceArray = json_decode($product->getPostcodeProdctPrice(),true);
                if($postcodeProdctPriceArray && is_array($postcodeProdctPriceArray) && !empty($postcodeProdctPriceArray)){
                    $zipCode = $_GET['address'];
                    $zipCodePrice = null;
                    foreach($postcodeProdctPriceArray as $value){

                        $postCodesValueArray = [];

                        if(isset($value['postcode']) && $value['postcode']){
                            $postCodesValueArray = array_map('trim', explode(',', $value['postcode']));
                        }

                        if($value && in_array($zipCode, $postCodesValueArray) && isset($value['price'])){
                            $zipCodePrice = $value['price'];
                        }
                    }

                    if($zipCodePrice != null  && !$specialPriceflag){

                        $deduct_price  = ($item->getCalculationPrice() - $zipCodePrice);
                        $deduct_price2  = ($deduct_price) * $item->getQty();
				#mail("er.bharatmali@gmail.com","666", $zipCodePrice);

                    }else{

                        $deduct_price  = $specialprice;
                        $deduct_price2  = ($deduct_price) * $item->getQty();
				#mail("er.bharatmali@gmail.com","777", $deduct_price);

                    }
                }
            }



            $eachItem["id"] = $item->getId();
            $eachItem["productId"] = $product->getId();
            $eachItem["sku"] = $this->helperCatalog->stripTags($item->getSku());
            $eachItem["qty"] = $item->getQty()*1;
            $eachItem["typeId"] = $item->getProductType();
            $price = $product->getPrice();
            if ($product->getTypeId() == "configurable") {
                $regularPrice = $product->getPriceInfo()->getPrice("regular_price");
                $price = $regularPrice->getAmount()->getBaseAmount();
            } elseif (empty($price)) {
                $price = 0.0;
            }


            if ($zipCodePrice != null) { 

            #mail("er.bharatmali@gmail.com","includind",$deduct_price);

                $eachItem["price"] =  $this->helper->getCurrencyConvertedFormattedPrice($item->getCalculationPrice()-$deduct_price);
                $eachItem["subTotal"] = $this->helper->getCurrencyConvertedFormattedPrice($item->getRowTotal() -$deduct_price);
                $eachItem["finalPrice"] =  $item->getRowTotal() -$deduct_price;
                $eachItem["formattedPrice"] = $this->helper->getCurrencyConvertedFormattedPrice(
                    $item->getCalculationPrice()-$deduct_price);
                $eachItem["formattedFinalPrice"] = $this->helper->getCurrencyConvertedFormattedPrice(
                    $item->getCalculationPrice()-$deduct_price);
	#mail("er.bharatmali@gmail.com","111", print_r( $eachItem, true ));

            } else {
		
                $eachItem["price"] = $this->catalogHelper->getTaxPrice($product, $price);
                $eachItem["subTotal"] = $this->helper->getCurrencyConvertedFormattedPrice($item->getRowTotalInclTax());
                $eachItem["finalPrice"] = $item->getPriceInclTax();
               $eachItem["formattedPrice"] = $this->helperCatalog->stripTags(
                        $this->priceHelper->currency($eachItem["price"])
                    );
                    $eachItem["formattedFinalPrice"] = $this->helper->getCurrencyConvertedFormattedPrice(
                        $item->getPriceInclTax()
                    );
#mail("er.bharatmali@gmail.com","222", print_r( $eachItem, true ));
            }
            if (!$this->helperCatalog->getIfTaxIncludeInPrice()) {
            #mail("er.bharatmali@gmail.com","includind", "ffda");

                if ($product->getPostcodeProdctPrice() && $zipCode) {
                    $eachItem["price"] = $this->helper->getCurrencyConvertedFormattedPrice($item->getCalculationPrice()-$deduct_price);
                    $eachItem["subTotal"] = $this->helper->getCurrencyConvertedFormattedPrice($item->getRowTotal() -$deduct_price);
                    $eachItem["finalPrice"] = $item->getRowTotal() -$deduct_price;
                    $eachItem["formattedPrice"] = $this->helperCatalog->stripTags(
                        $this->priceHelper->currency($item->getCalculationPrice()-$deduct_price)
                    );
                    $eachItem["formattedFinalPrice"] = $this->helper->getCurrencyConvertedFormattedPrice(
                       $item->getCalculationPrice()-$deduct_price
                    );
                } else {
           # mail("er.bharatmali@gmail.com","incasdsadludind", "ffda");


                    $eachItem["price"] = $this->catalogHelper->getTaxPrice($product, $price);
                    $eachItem["subTotal"] = $this->helper->getCurrencyConvertedFormattedPrice($item->getRowTotalInclTax());
                    $eachItem["finalPrice"] = $item->getPriceInclTax();
                    $eachItem["formattedPrice"] = $this->helperCatalog->stripTags(
                        $this->priceHelper->currency($eachItem["price"])
                    );
                    $eachItem["formattedFinalPrice"] = $this->helper->getCurrencyConvertedFormattedPrice(
                        $item->getPriceInclTax()
                    );
                }
            }                   

           # mail("er.bharatmali@gmail.com","tets", print_r( $eachItem, true ));
                    
            $eachItem["previous_order_expiry_date"] = $product->getPreviousOrderExpiryDate();
            $todate = $product->getSpecialToDate();
            $fromdate = $product->getSpecialFromDate();

            $eachItem["isInRange"] = $this->helperCatalog->getIsInRange($todate, $fromdate);

            $eachItem["groupedProductId"] = 0;
            if ($item->getProductType() == "grouped") {
                if (!empty($options["super_product_config"]) && !empty($options["super_product_config"]["product_id"])
                ) {
                    $eachItem["groupedProductId"] = $options["super_product_config"]["product_id"];
                }
            }
            $baseMessages = $item->getMessage(false);
            if ($baseMessages) {
                foreach ($baseMessages as $message) {
                    $messages = ["text"=>$message, "type"=>$item->getHasError() ? "error" : "notice"];
                    $eachItem["messages"][] = $messages;
                }
            } else {
                $eachItem["messages"] = [];
            }
            $productData = $productRepository->getById($product->getId());
            $stockData = $this->stockRegistry->getStockItem($product->getId());
            if (!$productData->isAvailable() && !$stockData->getBackorders() && !$eachItem["messages"]) {
                $messages = ["text" => __("Some of %1 product is out of stock.", $productData->getName()), "type" => "error"];
                $eachItem["messages"][] =$messages;
            }

            if ($product->getTypeId() == "configurable") {
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
                if ($eachItem["qty"] < $childProduct->getQty() && !$stockData->getBackorders()) {
                    $messages = ["text" => __("Some of %1 product is out of stock.", $childProduct->getName()), "type" => "error"];
                    $eachItem["messages"][] =$messages;
                    break;
                }else {
                    $stockData = $this->stockRegistry->getStockItem($childProduct->getId());
                    $availableQty = $stockData->getQty();  
                   # mail("er.bharatmali@gmail.com","availableQty",$childProduct->getId()."->".$availableQty);
                   # mail("er.bharatmali@gmail.com","getQty()",$childProduct->getId()."->".$item->getQty());
                    if ($item->getQty() <= 0) {
                        $messages = ["text" => __("The requested quantity is not available.", $childProduct->getName()), "type" => "error"];
                        $eachItem["messages"][] =$messages;

                    }
                }
            }

            $stockData = $this->stockRegistry->getStockItem($product->getId());
            $availableQty = $stockData->getQty();  
            if ($product->getTypeId() != "configurable" && $availableQty < $item->getQty()  && !$stockData->getBackorders()) {

                $messages = ["text" => __("The requested quantity is not available.", $product->getName()), "type" => "error"];
                $eachItem["messages"][] =$messages;

            }

            if($stockData->getBackorders() == 1 || $stockData->getBackorders() == 2){
                $eachItem["preorderMessage"] = "This item is being pre-ordered now.";
            }

       # mail("er.bharatmali@gmail.com","backorder",print_r($eachItem,true));
            $this->items[] = $eachItem;
        }
        $cartItemData["items"] = $this->items;
        return $cartItemData;
    }

    protected function getdeductFinalPrice(){
        $deduct_price = 0;
        $total_deduct_price = 0;
        $zipCode = $_GET['address'];
        $itemCollection = $this->itemCollectionFactory->create()->setQuote($this->quote);
        $itemCollection->addFieldToFilter("parent_item_id", ["null" => true]);
        foreach ($itemCollection as $item) {
            $product = $item->getProduct();
            $product->load($product->getId());
            if ($product->getPostcodeProdctPrice() && $zipCode) {
                $postcodeProdctPriceArray = json_decode($product->getPostcodeProdctPrice(),true);
                if($postcodeProdctPriceArray && is_array($postcodeProdctPriceArray) && !empty($postcodeProdctPriceArray)){
                    $zipCode = $_GET['address'];
                    $zipCodePrice = null;
                    foreach($postcodeProdctPriceArray as $value){

                        $postCodesValueArray = [];

                        if(isset($value['postcode']) && $value['postcode']){
                            $postCodesValueArray = array_map('trim', explode(',', $value['postcode']));
                        }

                        if($value && in_array($zipCode, $postCodesValueArray) && isset($value['price'])){
                            $zipCodePrice = $value['price'];
                        }
                    }

                    if($zipCodePrice != null){
                        $deduct_price  = $item->getCalculationPrice() - $zipCodePrice;
                        $total_deduct_price += $deduct_price * $item->getQty();
                    }
                }
            }
        }

        return $total_deduct_price;
    }

    /**
    * Function to get cross Sell Product List
    *
    * @return void
    */
    protected function getCrossSellList()
    {
        $crossSellList = [];
        if ($this->cartProductIds) {
            $filterProductIds = array_merge(
                $this->cartProductIds,
                $this->relatedProducts->getRelatedProductIds($this->quote->getAllItems())
            );
            $collection = $this->catalogLink
            ->create()
            ->useCrossSellLinks()
            ->getProductCollection()
            ->setStoreId($this->storeId)
            ->addStoreFilter()
            ->setPageSize(4)
            ->setVisibility($this->productVisibility->getVisibleInCatalogIds())
            ->addProductFilter($filterProductIds)
            ->addExcludeProductFilter($this->cartProductIds)
            ->setPageSize(4 - count($this->items))
            ->setGroupBy()
            ->setPositionOrder();
            foreach ($collection as $eachProduct) {
                $eachProduct = $this->productFactory->create()->load($eachProduct->getId());
                if ($eachProduct->isAvailable()) {
                    $crossSellList[] = $this->helperCatalog->getOneProductRelevantData(
                        $eachProduct,
                        $this->storeId,
                        $this->width,
                        $this->customerId
                    );
                }
            }
        }
        return $crossSellList;
    }

    /**
    * Function to get information about Total
    *
    * @return array
    */
    protected function getTotalsData()
    {
        $totals = [];
        $totalsData = [];
        if ($this->quote->isVirtual()) {
            $totals = $this->quote->getBillingAddress()->getTotals();
        } else {
            $totals = $this->quote->getShippingAddress()->getTotals();
        }
        $subtotal = [];
        $discount = [];
        $grandtotal = [];
        $shipping = [];
        if (isset($totals["subtotal"])) {
            $subtotal = $totals["subtotal"];
            $totalsData["subtotal"]["title"] = $subtotal->getTitle();
            $totalsData["subtotal"]["value"] = $this->helper->getCurrencyConvertedFormattedPrice($subtotal->getValue());
            $totalsData["subtotal"]["formattedValue"] = $this->helper->getCurrencyConvertedFormattedPrice(
                $subtotal->getValue()
            );
            $totalsData["subtotal"]["unformattedValue"] = $subtotal->getValue() - $this->getdeductFinalPrice();
        }
        if (isset($totals["discount"])) {
            $discount = $totals["discount"];
            $totalsData["discount"]["title"] = $discount->getTitle();
            $totalsData["discount"]["value"] = $this->helper->getCurrencyConvertedFormattedPrice($discount->getValue());
            $totalsData["discount"]["formattedValue"] = $this->helper->getCurrencyConvertedFormattedPrice(
                $discount->getValue()
            );
            $totalsData["discount"]["unformattedValue"] = $discount->getValue() ;
        }
        if (isset($totals["shipping"])) {
            $shipping = $totals["shipping"];
            $totalsData["shipping"]["title"] = $shipping->getTitle();
            $totalsData["shipping"]["value"] = $this->helper->getCurrencyConvertedFormattedPrice($shipping->getValue());
            $totalsData["shipping"]["formattedValue"] = $this->helper->getCurrencyConvertedFormattedPrice(
                $shipping->getValue()
            );
            $totalsData["shipping"]["unformattedValue"] = $shipping->getValue();
        }
        if (isset($totals["tax"])) {
            $tax = $totals["tax"];
            // $totalsData["tax"]["title"] = $tax->getTitle();
            $totalsData["tax"]["title"] =  __("Tax (inclusive)");
            $totalsData["tax"]["value"] = $this->helper->getCurrencyConvertedFormattedPrice($tax->getValue());
            $totalsData["tax"]["formattedValue"] = $this->helper->getCurrencyConvertedFormattedPrice($tax->getValue());
            $totalsData["tax"]["unformattedValue"] = $tax->getValue()  ;
        }
        if (isset($totals["grand_total"])) {
            $grandtotalexc = $totals["subtotal"];

            // $totalsData["subtotal"]["title"] = __("Grand total( Excl.Tx )");
            $totalsData["subtotal"]["title"] = __("Sub total");
            $totalsData["subtotal"]["value"] = $this->helper->getCurrencyConvertedFormattedPrice(
                $subtotal->getValue()
            );
            $totalsData["subtotal"]["formattedValue"] = $this->helper->getCurrencyConvertedFormattedPrice(
                $subtotal->getValue());

            $grandtotal = $totals["grand_total"];
            $totalsData["grandtotal"]["title"] = __("Grand total( incl.Tx )");
            //$totalsData["grandtotal"]["title"] = __("Tax (inclusive)");
            $totalsData["grandtotal"]["value"] = $this->helper->getCurrencyConvertedFormattedPrice(
                $grandtotal->getValue()
            );
            $totalsData["grandtotal"]["formattedValue"] = $this->helper->getCurrencyConvertedFormattedPrice(
                $grandtotal->getValue()
            );
            $this->returnArray["cartTotal"] = $this->helper->getCurrencyConvertedFormattedPrice(
                $grandtotal->getValue()
            );

            $this->returnArray["unformattedCartTotal"] = (float)$grandtotal->getValue();
            $totalsData["grandtotal"]["unformattedValue"] = $grandtotal->getValue();
        }


        return $totalsData;
    }
}
