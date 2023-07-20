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
* Class ReviewAndPayment
* To get order review data and available payment methods
*/
class ReviewAndPayment extends AbstractCheckout
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $this->customerSession->setId($this->customerId);
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $store = $this->store;
            $baseCurrency = $store->getBaseCurrencyCode();
            $this->currency = $this->wholeData["currency"] ?? $baseCurrency;
            $store->setCurrentCurrencyCode($this->currency);
            $quote = new \Magento\Framework\DataObject();
            if ($this->customerId != 0) {
                $quote = $this->helper->getCustomerQuote($this->customerId);
            }
            if ($this->quoteId != 0) {
                $quote = $this->quoteFactory->create()->setStoreId($this->storeId)->load($this->quoteId);
            }
            if ($quote->getItemsQty()*1 == 0) {
                $this->returnArray["message"] = __("Sorry Something went wrong !!");
                return $this->getJsonResponse($this->returnArray);
            } else {
                $this->returnArray["cartCount"] = $quote->getItemsQty()*1;
            }
            // validate minimum amount check ////////////////////////////////////////
            $isCheckoutAllowed = $quote->validateMinimumAmount();
            if (!$isCheckoutAllowed) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($this->helper->getConfigData("sales/minimum_order/description"))
                );
            }
            if ($this->shippingMethod != "") {
                $shippingMethod = $this->shippingMethod;
                $rate = $quote->getShippingAddress()->getShippingRateByCode($shippingMethod);
                if (!$rate) {
                    $returnArray["message"] = __("Invalid shipping method.");
                    return $this->getJsonResponse($returnArray);
                }
                $quote->getShippingAddress()->setShippingMethod($this->shippingMethod);
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->collectTotals()->save();
            }

            /* Code Start */ 
            $helper = $this->_objectManager->create('\Magecomp\Paymentfee\Helper\Data');
            if($helper->isEnabled()){
                $this->selectedMethod = $this->wholeData['selectedMethod'] ?? "";
                $fee = $helper->getApiQuoteFees($quote,$this->selectedMethod);
                $address = $quote->getShippingAddress();
                $address->setMcPaymentfeeAmount($fee);
                $address->setBaseMcPaymentfeeAmount($fee);

                $grandtotal = ($address->getSubtotal() + $address->getTaxAmount() + $address->getShippingAmount() + $fee) - $address->getDiscountAmount();
              #  mail("er.bharatmali@gmail.com","test",$grandtotal);
                if ($quote->isVirtual()) {
                    $totals = $quote->getBillingAddress()->getTotals();
                } else {
                    $totals = $quote->getShippingAddress()->getTotals();
                }
                if (isset($totals)) {
                    $grandtotal = $totals["subtotal"]['value'];
                    if (isset($totals["discount"])) {
                        $grandtotal = $grandtotal + $address->getDiscountAmount();
                      //  $grandtotal = $grandtotal;
                    }
                    if (isset($totals["shipping"])) {
                        $grandtotal = $grandtotal + $totals["shipping"]['value'];
                    }
                    $grandtotal = $grandtotal + $fee;
                }

                $address->setGrandTotal($grandtotal);
                $address->setBaseGrandTotal($grandtotal);
                $address->save();

                $quote->setGrandTotal($grandtotal);
                $quote->setBaseGrandTotal($grandtotal);

                // $quote->collectTotals()->save();
            }
            /* Code End */       



            $orderReviewData = $this->getOrderReviewData($quote);
            $this->getPaymentMethods($quote);
            $this->returnArray["success"] = true;
            $this->returnArray['couponCode'] = $quote->getCouponCode()??'';
            $this->returnArray["currencyCode"] = $this->storeManager->getStore()->getCurrentCurrencyCode();
            $this->returnArray["orderReviewData"] = $orderReviewData;
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
            $this->shippingMethod = $this->wholeData["shippingMethod"] ?? "";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->selectedMethod = $this->wholeData['selectedMethod'] ?? "";
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
    * Function to populate return array with payment methods
    *
    * @param \Magento\Framework\DataObject $quote quote
    *
    * @return void
    */
    protected function getPaymentMethods($quote)
    {
        $paymentDetails = $this->paymentDetails
        ->setPaymentMethods(
            $this->paymentMethodInterface->getList($quote->getId())
        )->getData();
        foreach ($paymentDetails["payment_methods"] as $method) {

            // if($method->getCode() =="webkul_stripe" || $method->getCode() == "mpstripe"){
            // continue;
            // }
            $oneMethod = [];
            $oneMethod["code"] = $method->getCode();
            $oneMethod["title"] = $method->getTitle();
            $oneMethod["extraInformation"] = "";
            if (in_array($method->getCode(), ["paypal_standard", "paypal_express"])) {
                $oneMethod["extraInformation"] = __("You will be redirected to the PayPal website.");
                $config = $this->paypalConfig->setMethod($method->getCode());
                $locale = $this->localeResolver;
                $oneMethod["title"] = "";
                $oneMethod["link"] = $config->getPaymentMarkWhatIsPaypalUrl($locale);
                $oneMethod["imageUrl"] = $config->getPaymentMarkImageUrl($locale->getDefaultLocale());
            } elseif (in_array($method->getCode(), ["paypal_express_bml"])) {
                $oneMethod["extraInformation"] = __("You will be redirected to the PayPal website.");
                $oneMethod["title"] = "";
                $oneMethod["link"] = "https://www.securecheckout.billmelater.com/paycapture-content/fetch".
                "?hash=AU826TU8&content=/bmlweb/ppwpsiw.html";
                $oneMethod["imageUrl"] = "https://www.paypalobjects.com/webstatic/en_US/i/buttons/".
                "ppc-acceptance-medium.png";
            } elseif ($method->getCode() == "checkmo") {
                if ($method->getPayableTo()) {
                    $extraInformationPrefix = __("Make Check payable to:");
                } else {
                    $extraInformationPrefix = __("Send Check to:");
                }
                $extraInformation = $this->helper->getConfigData("payment/".$method->getCode()."/mailing_address");
                if ($extraInformation == "") {
                    $extraInformation = __(" xxxxxxx");
                }
                $oneMethod["extraInformation"] = $extraInformationPrefix.$extraInformation;
            } elseif ($method->getCode() == "banktransfer") {
                $extraInformation = $this->helper->getConfigData("payment/".$method->getCode()."/instructions");
                if ($extraInformation == "") {
                    $extraInformation = __("Bank Details are xxxxxxx");
                }
                // $bankDetails = "";


                $items = $quote->getAllVisibleItems();
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

                if (empty($items)) {
                    $items = $objectManager->get('Magento\Checkout\Model\Session')->getLastRealOrder()->getAllVisibleItems();
                }
                if (!empty($items)) {
                    foreach ($items as $item) {
                        $productId = $item->getProductId();
                        $mpHelper = $objectManager->get('Webkul\Marketplace\Helper\Data');
                        $sellerId = $objectManager->get('Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory')->create()
                        ->addFieldToFilter('mageproduct_id', $productId)
                        ->setPageSize(1)
                        ->getFirstItem()
                        ->getSellerId();
                        if (!empty($sellerId)) {
                            $bankDetails = $mpHelper->getSellerCollectionObj($sellerId)->setPageSize(1)
                            ->getFirstItem()
                            ->getBankDetails();
                            if (!empty($extraInformation)) {
                                $extraInformation = $bankDetails;
                            }
                        }
                        break;
                    }
                }
                # mail("er.bharatmali@gmail.com","Bank transfer",$bankDetails);
                $oneMethod["extraInformation"] = $extraInformation;
            } elseif ($method->getCode() == "cashondelivery") {
                $extraInformation = $this->helper->getConfigData("payment/".$method->getCode()."/instructions");
                if ($extraInformation == "") {
                    $extraInformation = __("Pay at the time of delivery");
                }
                $oneMethod["extraInformation"] = $extraInformation;
            } elseif (in_array($method->getCode(), ["webkul_stripe", "authorizenet"])) {
                $allowedCc = [];
                $allowedCcTypesString = $method->getConfigData("cctypes");
                $allowedCcTypes = explode(",", $allowedCcTypesString);
                $ccTypes = $this->ccType->toOptionArray();
                $types = [];
                foreach ($ccTypes as $data) {
                    if (isset($data["value"]) && isset($data["label"])) {
                        $types[$data["value"]] = $data["label"];
                    }
                }
                foreach ($allowedCcTypes as $value) {
                    $eachAllowedCc = [];
                    $eachAllowedCc["code"] = $value;
                    $eachAllowedCc["name"] = $types[$value];
                    $allowedCc[] = $eachAllowedCc;
                }
                $extraInformation = $allowedCc;
                $oneMethod["extraInformation"] = $extraInformation;
            } elseif($method->getCode() == "mpstripe" && $this->selectedMethod == "mpstripe"){
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $mpstripeHelper = $objectManager->get('Webkul\MpStripe\Helper\Data');
                $control = $objectManager->create(\Webkul\MpStripe\Controller\Payment\CreateIntentFactory::class);
                $encryptor = $objectManager->get('Magento\Framework\Encryption\EncryptorInterface');
                $oneMethod["apiKey"] = $mpstripeHelper->getConfigValue("api_key");
                \Stripe\Stripe::setApiKey($oneMethod["apiKey"]);
                $amount = number_format((float)$quote->getGrandTotal(), 2, '.', '');
                $amount = str_replace('.', '', $amount);
                $this->coreSession = $this->_objectManager->create(\Magento\Checkout\Model\Session::class);
                $finalCart = $mpstripeHelper->getFinalCart($quote);
                $paymentDetails = $mpstripeHelper->getCheckoutFinalData($finalCart, $quote);
                $this->coreSession->setPaymentDetails($paymentDetails);
                $ifSellerInCart = $mpstripeHelper->getIfSellerInCart($paymentDetails);
                $client_secret = "";
                if ($ifSellerInCart) {
                    $transfer = $this->createStripeTransfer(
                        $paymentDetails
                    );
                    $client_secret = $transfer->client_secret;
                    $oneMethod["client_secret"] =  $client_secret;
                } else {
                    $intent = \Stripe\PaymentIntent::create([
                        'amount' => (int)$amount,
                        'currency' => $quote->getBaseCurrencyCode(),
                        'payment_method_types' => ['card'],
                    ]);
                    $oneMethod["client_secret"] =  $intent->client_secret;
                }
                $connectionToken = \Stripe\Terminal\ConnectionToken::create();
                $oneMethod["token"] = $connectionToken->secret;
                $oneMethod["publishable_key"] = $mpstripeHelper->getConfigValue("api_publish_key");
                $oneMethod["debug"] = $this->helper->getConfigData("payment/".$method->getCode()."/debug");
                $oneMethod["integration"] = $this->helper->getConfigData("payment/".$method->getCode()."/integration") == 1 ? "Authorize" : "Authorize and Capture";
                $oneMethod["paymentAction"] = $mpstripeHelper->getConfigValue("stripe_payment_action");
                $oneMethod["clientSecret"] = $mpstripeHelper->getConfigValue("client_secret");
                $oneMethod["stripeAccount"] = $encryptor->decrypt($mpstripeHelper->getConfigValue("stripe_account")); 
                $oneMethod['quoteId'] = $quote->getId();
            } elseif($method->getCode() == "mppaypalexpresscheckout" && $this->selectedMethod == "mppaypalexpresscheckout") {
                $urlInterface = $this->_objectManager->create("\Magento\Framework\UrlInterface");
                $this->returnArray["webview"] = true;
                $this->returnArray["redirectUrl"] = $urlInterface->getUrl("mobikulhttp/checkout/paypalredirect", ["storeId" => $this->storeId, "quoteId" => $quote->getId()]);
                $this->returnArray["successUrl"] = [
                    $urlInterface->getUrl("checkout/onepage/success")
                ];
                $this->returnArray["cancelUrl"] = [
                    $urlInterface->getUrl("checkout/cart")
                ];
                $this->returnArray["failureUrl"] = [
                    $urlInterface->getUrl("checkout/onepage/failure")
                ];
            }
            $this->returnArray["paymentMethods"][] = $oneMethod;
        }
    }

    public function createStripeTransfer($paymentDetailsArray)
    {
        // try {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $mpstripeHelper = $objectManager->get('Webkul\MpStripe\Helper\Data');
        $apiKey = $mpstripeHelper->getConfigValue("api_key");

        $finalArray = [];
        $finalArray["payment_method_types"] = ["card"];
        $finalArray['amount'] = 0;
        foreach ($paymentDetailsArray as $paymentDetails) {
            $finalArray['amount'] += $paymentDetails['payment_array']['amount'];
            $finalArray['currency'] = $paymentDetails['payment_array']['currency'];
            $finalArray['transfer_group'] = $paymentDetails['payment_array']['order_id'];
        }
        // Create a PaymentIntent:
        $intenet = \Stripe\PaymentIntent::create(
            $finalArray
        );
        foreach ($paymentDetailsArray as $paymentDetails) {
            if ($paymentDetails['cart']['stripe_user_id'] != "") {
                $transfer = \Stripe\Transfer::create([
                    'amount' => $paymentDetails['cart']['price'],
                    'currency' => $paymentDetails['payment_array']['currency'],
                    'destination' => $paymentDetails['cart']['stripe_user_id'],
                    'transfer_group' => $paymentDetails['payment_array']['order_id']
                ]);
            }
        }
        return $intenet;
        // } catch (\Stripe\Error $e) {
        //     $this->logger->critical($e->getMessage()."267");
        //     return false;
        // }
    }


    public function arrayMerge($arr1, $arr2)
    {
        return array_merge($arr1, $arr2);
    }

    /**
    * Function to get order Review Data
    *
    * @param \Magento\Quote\Model\Quote $quote quote
    *
    * @return array
    */
    public function getdeductPrice(){
        $this->verifyRequest();
        $this->customerSession->setId($this->customerId);
        $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
        $store = $this->store;
        $baseCurrency = $store->getBaseCurrencyCode();
        $this->currency = $this->wholeData["currency"] ?? $baseCurrency;
        $store->setCurrentCurrencyCode($this->currency);
        $quote = new \Magento\Framework\DataObject();
        if ($this->customerId != 0) {
            $quote = $this->helper->getCustomerQuote($this->customerId);
        }
        $shippingAddress = $quote->getShippingAddress();
        $zipCode = $shippingAddress->getPostcode();
        $product_id = "";
        $PostcodeProdctPrice = "";
        $total_deduct_price = 0;
        $deduct_price = 0;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $context = $objectManager->get('Magento\Framework\App\Http\Context');
        $isLoggedIn = $context->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        $itemsCollection = $cart->getQuote()->getItemsCollection()->addFieldToFilter('quote_id', $quote->getId());
        foreach($itemsCollection->getData() as $item) 
        {
            $product_id .= $item['price'].',';
            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item['product_id']);
            if ($product->getPostcodeProdctPrice() && $zipCode) {
                $postcodeProdctPriceArray = json_decode($product->getPostcodeProdctPrice(),true);
                if($postcodeProdctPriceArray && is_array($postcodeProdctPriceArray) && !empty($postcodeProdctPriceArray)){
                    $zipCode = $shippingAddress->getPostcode();
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
                        $deduct_price  = $item['price'] - $zipCodePrice;
                        $total_deduct_price += $deduct_price * $item['qty'];
                    }
                }
            }
        }
        return $total_deduct_price;
    }
    public function getOrderReviewData($quote)
    {
        $productRepository = $this->_objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
        $orderReviewData = [];
        $deduct_price = 0;
        $deduct_price2 = 0;
        $outOfStock = false;
        foreach ($quote->getAllVisibleItems() as $item) {
            $eachItem = [];
            $eachItem["productName"] = $this->helperCatalog->stripTags($item->getName());
            $customoptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
            $result = [];
            if ($customoptions) {
                if (isset($customoptions["options"])) {
                    $result = $this->arrayMerge($result, $customoptions["options"]);
                }
                if (isset($customoptions["additional_options"])) {
                    $result = $this->arrayMerge($result, $customoptions["additional_options"]);
                }
                if (isset($customoptions["attributes_info"])) {
                    $result = $this->arrayMerge($result, $customoptions["attributes_info"]);
                }
            }
            if ($result) {
                foreach ($result as $option) {
                    $eachOption = [];
                    $eachOption["label"] = htmlspecialchars_decode($option["label"]);
                    if (is_array($option["value"])) {
                        $eachOption["value"] = $option["value"];
                    } else {
                        $eachOption["value"][] = $this->helperCatalog->stripTags($option["value"]);
                    }
                    $eachItem["option"][] = $eachOption;
                }
            }
            $product = $item->getProduct();

            $price = $product->getPrice();
            if ($product->getTypeId() == "configurable") {
                $regularPrice = $product->getPriceInfo()->getPrice("regular_price");
                $price = $regularPrice->getAmount()->getBaseAmount();
            } elseif (empty($price)) {
                $price = 0.0;
            }
            
            $orgprice = $product->getPrice();
            $specialprice = $product->getSpecialPrice();
            $specialfromdate =$product->getSpecialFromDate();
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

            if ($product) {
                $this->cartProductIds[] = $product->getId();
            }
            $shippingAddress = $quote->getShippingAddress();
            $zipCode = $shippingAddress->getPostcode();
            $product->load($product->getId());
            if ($product->getPostcodeProdctPrice() && $zipCode) {
                $postcodeProdctPriceArray = json_decode($product->getPostcodeProdctPrice(),true);
                if($postcodeProdctPriceArray && is_array($postcodeProdctPriceArray) && !empty($postcodeProdctPriceArray)){
                    $zipCode = $shippingAddress->getPostcode();
                    $zipCodePrice = null;
                    foreach($postcodeProdctPriceArray as $value){

                        $postCodesValueArray = [];

                        if(isset($value['postcode']) && $value['postcode']){
                            $postCodesValueArray = array_map('trim', explode(',', $value['postcode']));
                        }
                        #mail("er.bharatmali@gmail.com","adsa",print_R($postCodesValueArray),true));
                        if($value && in_array($zipCode, $postCodesValueArray) && isset($value['price'])){
                            $zipCodePrice = $value['price'];
                        }
                    }

                    if($zipCodePrice != null && !$specialPriceflag){
                        $deduct_price  = ($item->getCalculationPrice() - $zipCodePrice);
                        $deduct_price2  = ($deduct_price) * $item->getQty();
				#mail("er.bharatmali@gmail.com","deduct_price2",$deduct_price2);

                        $eachItem["price"] = $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($item->getCalculationPrice() - $deduct_price));
                        $eachItem["subTotal"] = $this->helperCatalog->stripTags(
                            $this->checkoutHelper->formatPrice($item->getRowTotal() - $deduct_price2)
                        );
                    }else{
                        $deduct_price  = $specialprice;
                        $deduct_price2  = ($deduct_price) * $item->getQty();
                       $eachItem["price"] = $this->helperCatalog->stripTags(
                            $this->checkoutHelper->formatPrice($deduct_price));
                        $eachItem["subTotal"] = $this->helperCatalog->stripTags(
                            $this->checkoutHelper->formatPrice($deduct_price2)
                        ); 
                    }
                }
            } else  {
            # mail("er.bharatmali@gmail.com","adsa",$item->getPriceInclTax());
                $eachItem["price"] = $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($item->getPriceInclTax()));
                $eachItem["subTotal"] = $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($item->getRowTotalInclTax())
                );
            }
            /*if($specialPriceflag){
                 $eachItem["price"] = $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($specialprice));
                $eachItem["subTotal"] = $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($item->getRowTotalInclTax())
                );
            }*/
            
                     

            $eachItem["qty"] = $item->getQty();
            $eachItem["previous_order_expiry_date"] = $product->getPreviousOrderExpiryDate();
            $eachItem["unformattedOriginalPrice"] =round($this->priceCurrencyInterface->convert(
                $item->getProduct()->getPrice(),
                $this->storeId,
                $this->currency
                ), 2);
            $eachItem["originalPrice"] = $this->helperCatalog->stripTags(
                $this->checkoutHelper->formatPrice(
                    $this->priceCurrencyInterface->convert($item->getProduct()->getPrice())
                ),
                $this->storeId,
                $this->currency
            );

            $eachItem["thumbNail"] = $this->helperCatalog->getImageUrl(
                $item->getProduct(),
                $this->width/2.5,
                "product_page_image_small"
            );
            $eachItem["dominantColor"] = $this->helper->getDominantColor(
                $this->helper->getDominantColorFilePath(
                    $this->helperCatalog->getImageUrl($item->getProduct(), $this->width/2.5, "product_page_image_small")
                )
            );
            $eachItem["unformattedPrice"] = $this->priceCurrencyInterface->convert(
                $item->getProduct()->getPrice());
            $baseMessages = $item->getMessage(false);
            if ($baseMessages) {
                foreach ($baseMessages as $message) {
                    $messages = ["text"=>$message, "type"=>$item->getHasError() ? "error" : "notice"];
                    $eachItem["messages"][] = $messages;
                    $eachItem['outofstock'] = true;
                    $outOfStock = true;
                }
            } else {
                $eachItem["messages"] = [];
                $eachItem["outofstock"] = false;
            }
            $product = $item->getProduct();
            $stockData = $this->stockRegistry->getStockItem($product->getId());
            $productData = $productRepository->getById($product->getId());
            if (!$productData->isAvailable() && !$stockData->getBackorders() && !$eachItem["messages"]) {
                $messages = ["text" => __("Some of %1 product is out of stock.", $productData->getName()), "type" => "error"];
                $eachItem["messages"][] =$messages;
                $eachItem['outofstock'] = true;
                $outOfStock = true;
            }
            if ($item->getProductType() == "configurable") {
                $productTypeInstance = $product->getTypeInstance();
                $usedProducts = $productTypeInstance->getUsedProducts($product);
                foreach ($usedProducts  as $child) {
                    if ($this->helperCatalog->stripTags($item->getSku()) == $child->getSku()) {
                        if (!$child->isAvailable()) {
                            $messages = ["text" => __("Some of %1 product is out of stock.", $child->getName()), "type" => "error"];
                            $eachItem["messages"][] =$messages;
                            $eachItem['outofstock'] = true;
                            $outOfStock = true;
                            break;
                        }

                        $stockData = $this->stockRegistry->getStockItem($child->getId());
                        $availableQty = $stockData->getQty();  

                        if ($item->getQty() <=0) {
                            $messages = ["text" => __("The requested quantity is not available.", $child->getName()), "type" => "error"];
                            $eachItem["messages"][] =$messages;
                            $eachItem['outofstock'] = true;
                            $outOfStock = true;

                        }

                    }
                }
            }

            $stockData = $this->stockRegistry->getStockItem($product->getId());
            $availableQty = $stockData->getQty();  


            if ($item->getProductType() != "configurable" && $availableQty < $item->getQty() && !$stockData->getBackorders()) {
                $messages = ["text" => __("The requested quantity is not available.", $product->getName()), "type" => "error"];
                $eachItem["messages"][] =$messages;
                $eachItem['outofstock'] = true;
                $outOfStock = true;

            }
            if($stockData->getBackorders() == 1 || $stockData->getBackorders() == 2){
                $eachItem["preorderMessage"] = "This item is being pre-ordered now.";
            }

            $orderReviewData["items"][]   = $eachItem;
        }
        
     #mail("er.bharatmali@gmail.com","tets", print_r( $orderReviewData, true ));

        $this->returnArray['outOfStock'] = $outOfStock;
        $address = $quote->getBillingAddress();
        if ($address instanceof \Magento\Framework\DataObject) {
            $this->returnArray["billingAddress"] = $address->format("html");
        }

        if (!$quote->isVirtual()) {
            $address = $quote->getShippingAddress();
            if ($address instanceof \Magento\Framework\DataObject) {
                $this->returnArray["shippingAddress"] = $address->format("html");
            }
            if ($this->shippingMethod = $quote->getShippingAddress()->getShippingDescription()) {
                $this->returnArray["shippingMethod"] = $this->helperCatalog->stripTags($this->shippingMethod);
            }
        }
        $totals = [];
        if ($quote->isVirtual()) {
            $totals = $quote->getBillingAddress()->getTotals();
        } else {
            $totals = $quote->getShippingAddress()->getTotals();
        }

        $orderReviewData["cartTotal"] = 0;
        if (isset($totals["subtotal"])) {
            $subtotal = $totals["subtotal"];
            $orderReviewData["totals"][] = [
                "title" => $subtotal->getTitle(),
                "value" => $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($subtotal->getValue())),
                "formattedValue" => $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($subtotal->getValue())
                ),
                "unformattedValue" => $subtotal->getValue() 
            ];
        }
        if (isset($totals["discount"])) {
            $discount = $totals["discount"];
            $orderReviewData["totals"][] = [
                "title" => $discount->getTitle(),
                "value" => $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($discount->getValue())),
                "formattedValue" => $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($discount->getValue())
                ),
                "unformattedValue" => $discount->getValue()
            ];
        }
        if (isset($totals["tax"])) {
            $tax = $totals["tax"];
            $orderReviewData["totals"][] = [
                //"title" => $tax->getTitle(),
                "title" => __("Tax (inclusive)"),
                "value" => $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($tax->getValue())),
                "formattedValue" => $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($tax->getValue())
                ),
                "unformattedValue" => $tax->getValue()
            ];
        }
        if (isset($totals["shipping"])) {
            $shipping = $totals["shipping"];
            $orderReviewData["totals"][] = [
                "title" => $shipping->getTitle(),
                "value" => $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($shipping->getValue())),
                "formattedValue" => $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($shipping->getValue())
                ),
                "unformattedValue" => $shipping->getValue()
            ];
        }
        if (isset($totals["grand_total"])) {
            $grandtotal = $totals["grand_total"];
            $orderReviewData["totals"][] = [
                // "title" => $grandtotal->getTitle(),
                "title" => __("Order Total"),
                "value" => $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($grandtotal->getValue())),
                "formattedValue" => $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($grandtotal->getValue())
                ),
                "unformattedValue" => $grandtotal->getValue()
            ];
            $this->returnArray["cartTotal"] = $this->helperCatalog->stripTags(
                $this->checkoutHelper->formatPrice($grandtotal->getValue())
            );
        }
        /* Code Start */ 
        $helper = $this->_objectManager->create('\Magecomp\Paymentfee\Helper\Data');
        if($helper->isEnabled()){
            if($quote->getShippingAddress()->getMcPaymentfeeDescription() || $quote->getShippingAddress()->getMcPaymentfeeAmount() > 0){
                $orderReviewData["totals"][] = [
                    "title" => __($quote->getShippingAddress()->getMcPaymentfeeDescription()),
                    "value" => $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($quote->getShippingAddress()->getMcPaymentfeeAmount())),
                    "formattedValue" => $this->helperCatalog->stripTags(
                        $this->checkoutHelper->formatPrice($quote->getShippingAddress()->getMcPaymentfeeAmount())
                    ),
                    "unformattedValue" => $quote->getShippingAddress()->getMcPaymentfeeAmount()
                ];
            }
        }
        /* Code End */ 

        if (!empty($orderReviewData)) {
            return $orderReviewData;
        } else {
            return new \stdClass();
        }
    }
}
