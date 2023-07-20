<?php
    /**
    * Webkul Software.
    *
    * @category Webkul
    *
    * @author    Webkul
    * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\MobikulMpHyperLocal\Controller\Checkout;

    class ShippingPaymentMethodInfo extends \Webkul\MobikulApi\Controller\Checkout\AbstractCheckout    {

        public function execute()   {
            $returnArray                    = [];
            $returnArray["message"]         = "";
            $returnArray["success"]         = false;
            $returnArray["cartCount"]       = 0;
            $returnArray["otherError"]      = "";
            $returnArray["paymentMethods"]  = [];
            $returnArray["shippingMethods"] = [];
            try {
                $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $wholeData       = $this->getRequest()->getPostValue();
                $this->headers  = $this->getRequest()->getHeaders();
                $this->helper->log(__CLASS__, "logClass", $wholeData);
                $this->helper->log($wholeData, "logParams", $wholeData);
                $this->helper->log($this->headers, "logHeaders", $wholeData);
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $authData    = $this->helper->isAuthorized($authKey);
                    if ($authData["code"] == 1) {
                        $quoteId        = $wholeData["quoteId"]        ?? 0;
                        $storeId        = $wholeData["storeId"]        ?? 1;
                        $billingData    = $wholeData["billingData"]    ?? "{}";
                        $shippingData   = $wholeData["shippingData"]   ?? "{}";
                        $checkoutMethod = $wholeData["checkoutMethod"] ?? "";
                        $customerToken  = $wholeData["customerToken"]  ?? '';
                        $customerId     = $this->helper->getCustomerByToken($customerToken) ?? 0;
                        $billingData    = $this->jsonHelper->jsonDecode($billingData);
                        $shippingData   = $this->jsonHelper->jsonDecode($shippingData);
                        $environment    = $this->emulate->startEnvironmentEmulation($storeId);
                        $store          = $this->store;
                        $baseCurrency   = $store->getBaseCurrencyCode();
                        $currency       = $wholeData["currency"] ?? $baseCurrency;
                        $store->setCurrentCurrencyCode($currency);
                        $quote          = new \Magento\Framework\DataObject();
// checking customer token //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if (!$customerId && $customerToken != "")  {
                            $returnArray["message"]    = __("As customer you are requesting does not exist, so you need to logout.");
                            $returnArray["otherError"] = "customerNotExist";
                            $customerId = 0;
                        }
// end checking customer token //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if ($customerId != 0) {
                            $quoteCollection = $this->quoteFactory->create()->getCollection()
                                ->addFieldToFilter("customer_id", $customerId)
                                ->addFieldToFilter("is_active", 1)
                                ->addOrder("updated_at", "DESC");
                            $quote = $quoteCollection->getFirstItem();
                        }
                        if ($quoteId != 0)
                            $quote = $this->quoteFactory->create()->setStoreId($storeId)->load($quoteId);
                        if($quote->getItemsQty()*1 == 0){
                            $returnArray["message"] = __("Sorry Something went wrong !!");
                            return $this->getJsonResponse($returnArray);
                        }
                        else{
                            $returnArray["cartCount"] = $quote->getItemsQty()*1;
                        }
                        // validate minimum amount check ////////////
                        $isCheckoutAllowed = $quote->validateMinimumAmount();
                        if (!$isCheckoutAllowed) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __($this->helper->getConfigData("sales/minimum_order/description"))
                            );
                        }
                        $useForShipping = 0;
                        if(!empty($billingData))    {
                            $saveInAddressBook = 0;
                            if(isset($billingData["newAddress"]["saveInAddressBook"]))
                                $saveInAddressBook = $billingData["newAddress"]["saveInAddressBook"];
                            if($checkoutMethod == "register")
                                $saveInAddressBook = 1;
                            if($billingData["useForShipping"] != "")
                                $useForShipping = $billingData["useForShipping"];
                            $addressId = 0;
                            if($billingData["addressId"] != "")
                                $addressId = $billingData["addressId"];
                            $quote->setCheckoutMethod($checkoutMethod)->save();
                            $newAddress = [];
                            if($billingData["newAddress"] != "")
                                if(!empty($billingData["newAddress"]))
                                    $newAddress = $billingData["newAddress"];
                            $address     = $quote->getBillingAddress();
                            $addressForm = $this->customerForm;
                            $addressForm->setFormCode("customer_address_edit")->setEntityType("customer_address");
                            if($addressId > 0) {
                                $customerAddress = $this->customerAddress->load($addressId)->getDataModel();
                                if($customerAddress->getId()) {
                                    if($customerAddress->getCustomerId() != $quote->getCustomerId()){
                                        $returnArray["message"] = __("Customer Address is not valid.");
                                        return $this->getJsonResponse($returnArray);
                                    }
                                    $address->importCustomerAddressData($customerAddress)->setSaveInAddressBook(0);
                                    $addressForm->setEntity($address);
                                    $addressErrors = $addressForm->validateData($address->getData());
                                    if($addressErrors !== true){
                                        $returnArray["message"] = implode(", ", $addressErrors);
                                        return $this->getJsonResponse($returnArray);
                                    }
                                }
                            }
                            else {
                                $addressForm->setEntity($address);
                                $addressData = [
                                    "firstname"  => $newAddress["firstName"],
                                    "lastname"   => $newAddress["lastName"],
                                    "middlename" => $newAddress["middleName"] ?? "",
                                    "prefix"     => $newAddress["prefix"]     ?? "",
                                    "suffix"     => $newAddress["suffix"]     ?? "",
                                    "company"    => $newAddress["company"],
                                    "street"     => $newAddress["street"],
                                    "city"       => $newAddress["city"],
                                    "country_id" => $newAddress["country_id"],
                                    "region"     => $newAddress["region"],
                                    "region_id"  => $newAddress["region_id"],
                                    "postcode"   => $newAddress["postcode"],
                                    "telephone"  => $newAddress["telephone"],
                                    "fax"        => $newAddress["fax"]
                                ];
                                $addressErrors  = $addressForm->validateData($addressData);
                                if($addressErrors !== true){
                                    $returnArray["message"] = implode(", ", $addressErrors);
                                    return $this->getJsonResponse($returnArray);
                                }
                                $addressForm->compactData($addressData);
                                $address->setCustomerAddressId(null);
                                $address->setSaveInAddressBook($saveInAddressBook);
                                $quote->setCustomerFirstname($newAddress["firstName"])->setCustomerLastname($newAddress["lastName"]);
                            }
                            if(in_array($checkoutMethod, ["register", "guest"])){
                                $websiteId = $this->storeManager->getStore()->getWebsiteId();
                                if (!empty($newAddress["email"]) && !\Zend_Validate::is($newAddress["email"], "EmailAddress")) {
                                    $returnArray["message"] = __("Invalid email format");
                                    return $this->getJsonResponse($returnArray);
                                }
                                if($this->customerFactory->create()->setWebsiteId($websiteId)->loadByEmail(trim($newAddress["email"]))->getId() > 0){
                                    $returnArray["message"] = __("Email already exist");
                                    return $this->getJsonResponse($returnArray);
                                }
                                $quote->setCustomerEmail(trim($newAddress["email"]));
                                $address->setEmail(trim($newAddress["email"]));
                            }
                            if(!$address->getEmail() && $quote->getCustomerEmail()){
                                $address->setEmail($quote->getCustomerEmail());
                            }
                            if(($validateRes = $address->validate()) !== true){
                                $returnArray["message"] = implode(",", $validateRes);
                                return $this->getJsonResponse($returnArray);
                            }
                            if(true !== ($result = $this->validateCustomerData($wholeData))) {
                                $returnArray["message"] = implode(",", $result);
                                return $this->getJsonResponse($returnArray);
                            }
                            if(!$quote->getCustomerId() && "register" == $quote->getCheckoutMethod()) {
                                if($this->customerEmailExists($address->getEmail(), $this->storeManager->getStore()->getWebsiteId())){
                                    $returnArray["message"] = __("This email already exist.");
                                    return $this->getJsonResponse($returnArray);
                                }
                            }
                            if(!$quote->isVirtual()) {
                                $usingCase = isset($useForShipping) ? (int)$useForShipping : 0;
                                switch($usingCase) {
                                    case 0:
                                        $shipping = $quote->getShippingAddress();
                                        $shipping->setSameAsBilling(0);
                                        $setStepDataShipping = 0;
                                        break;
                                    case 1:
                                        $billing = clone $address;
                                        $billing->unsAddressId()->unsAddressType();
                                        $shipping = $quote->getShippingAddress();
                                        $shippingMethod = $shipping->getShippingMethod();
                                        $shipping->addData($billing->getData())
                                            ->setSameAsBilling(1)
                                            ->setSaveInAddressBook(0)
                                            ->setShippingMethod($shippingMethod)
                                            ->setCollectShippingRates(true);
                                        $setStepDataShipping = 1;
                                        break;
                                }
                            }
                            $quote->collectTotals()->save();
                            if(!$quote->isVirtual() && $setStepDataShipping)
                                $quote->getShippingAddress()->setCollectShippingRates(true);
                        }
                        else{
                            $returnArray["message"] = __("Invalid Billing data.");
                            return $this->getJsonResponse($returnArray);
                        }
// step 4 process starts here ///////////////////////////////////////////////////////////////////////////////////////////////////
                        if(!$quote->isVirtual()){
                            if($useForShipping == 0){
                                if($shippingData != ""){
                                    $sameAsBilling = 0;
                                    if($shippingData["sameAsBilling"] != "")
                                        $sameAsBilling = $shippingData["sameAsBilling"];
                                    $newAddress = [];
                                    if($shippingData["newAddress"] != "")
                                        if(!empty($shippingData["newAddress"]))
                                            $newAddress = $shippingData["newAddress"];
                                    $addressId = 0;
                                    if($shippingData["addressId"] != "")
                                        $addressId = $shippingData["addressId"];
                                    $saveInAddressBook = 0;
                                    if(isset($shippingData["newAddress"]["saveInAddressBook"]) && $shippingData["newAddress"]["saveInAddressBook"] != "")
                                        $saveInAddressBook = $shippingData["newAddress"]["saveInAddressBook"];
                                    $address = $quote->getShippingAddress();
                                    $addressForm = $this->customerForm;
                                    $addressForm->setFormCode("customer_address_edit")->setEntityType("customer_address");
                                    if($addressId > 0) {
                                        $customerAddress = $this->customerAddress->load($addressId)->getDataModel();
                                        if($customerAddress->getId()) {
                                            if($customerAddress->getCustomerId() != $quote->getCustomerId()){
                                                $returnArray["message"] = __("Customer Address is not valid.");
                                                return $this->getJsonResponse($returnArray);
                                            }
                                            $address->importCustomerAddressData($customerAddress)->setSaveInAddressBook(0);
                                            $addressForm->setEntity($address);
                                            $addressErrors  = $addressForm->validateData($address->getData());
                                            if($addressErrors !== true){
                                                $returnArray["message"] = implode(", ", $addressErrors);
                                                return $this->getJsonResponse($returnArray);
                                            }
                                        }
                                    }
                                    else {
                                        $addressForm->setEntity($address);
                                        $addressData = [
                                            "firstname"  => $newAddress["firstName"],
                                            "lastname"   => $newAddress["lastName"],
                                            "middlename" => $newAddress["middleName"] ?? "",
                                            "prefix"     => $newAddress["prefix"]     ?? "",
                                            "suffix"     => $newAddress["suffix"]     ?? "",
                                            "company"    => $newAddress["company"],
                                            "street"     => $newAddress["street"],
                                            "city"       => $newAddress["city"],
                                            "country_id" => $newAddress["country_id"],
                                            "region"     => $newAddress["region"],
                                            "region_id"  => $newAddress["region_id"],
                                            "postcode"   => $newAddress["postcode"],
                                            "telephone"  => $newAddress["telephone"],
                                            "fax"        => $newAddress["fax"]
                                        ];
                                        $addressErrors = $addressForm->validateData($addressData);
                                        if($addressErrors !== true){
                                            $returnArray["message"] = implode(", ", $addressErrors);
                                            return $this->getJsonResponse($returnArray);
                                        }
                                        $addressForm->compactData($addressData);
                                        $address->setCustomerAddressId(null);
// Additional form data, not fetched by extractData (as it fetches only attributes) /////////////////////////////////////////////
                                        $address->setSaveInAddressBook($saveInAddressBook);
                                        $address->setSameAsBilling($sameAsBilling);
                                    }
                                    // $address->implodeStreetAddress();
                                    $address->setCollectShippingRates(true);
                                    if(($validateRes = $address->validate()) !== true){
                                        $returnArray["message"] = implode(", ", $validateRes);
                                        return $this->getJsonResponse($returnArray);
                                    }
                                    $quote->collectTotals()->save();
                                }
                                else{
                                    $returnArray["message"] = __("Invalid Shipping data.");
                                    return $this->getJsonResponse($returnArray);
                                }
                            }
// Hyperlocal Address check ////////////////////////////////////////////////////////////////////////////////////////////////////////                                    
                            if (!$quote->isVirtual()) {
                                $shipAddress = $this->objectManager->create("Webkul\MpHyperLocal\Helper\Data")->getSavedAddress();
                                $quoteShipAdd = $quote->getShippingAddress()->getData();      
                                unset($quoteShipAdd['extension_attributes']);
                                unset($quoteShipAdd['applied_taxes']);
                                unset($quoteShipAdd['items_applied_taxes']);
                                unset($quoteShipAdd['cached_items_all']);
                                unset($quoteShipAdd['cart_fixed_rules']);
                                unset($quoteShipAdd['extra_taxable_details']);
                                $quoteShipAdd['country'] = $this->country->load($quoteShipAdd['country_id'])->getName();
                                $quoteShipAdd['street'] = trim(preg_replace('/\s+/', ' ', $quoteShipAdd['street']));
                                $quoteShipAdd = str_replace(',', ' ', implode(" ", $quoteShipAdd));
                                $quoteShipAdd = explode(' ', strtolower($quoteShipAdd));
                                $shipAddress['address'] = str_replace(
                                    strrchr($shipAddress['address'], ','),
                                    '',
                                    $shipAddress['address']
                                );
                                $shipAddress = explode(' ', str_replace(', ', ' ', $shipAddress['address']));
                                foreach ($shipAddress as $value) {
                                    if (in_array(strtolower($value), $quoteShipAdd) === false) {
                                        throw new \Magento\Framework\Exception\LocalizedException(__('Shipping address is not same as address selected.'));
                                    }
                                }
                            }
                            $quote->getShippingAddress()->collectShippingRates()->save();
                            $shippingRateGroups = $quote->getShippingAddress()->getGroupedAllShippingRates();
                            foreach($shippingRateGroups as $code => $rates) {
                                $oneShipping = [];
                                $oneShipping["title"] = $this->helperCatalog->stripTags($this->helper->getConfigData("carriers/".$code."/title"));
                                foreach($rates as $rate){
                                    $oneMethod = [];
                                    if($rate->getErrorMessage())
                                        $oneMethod["error"] = $rate->getErrorMessage();
                                    $oneMethod["code"]  = $rate->getCode();
                                    $oneMethod["label"] = $rate->getMethodTitle();
                                    $oneMethod["price"] = $this->helperCatalog->stripTags($this->priceHelper->currency((float) $rate->getPrice()));
                                    $oneShipping["method"][] = $oneMethod;
                                }
                                $returnArray["shippingMethods"][] = $oneShipping;
                            }
                        }
                        $paymentDetails = $this->paymentDetails
                            ->setPaymentMethods(
                                $this->paymentMethodInterface->getList($quote->getId())
                            )->getData();
                        foreach($paymentDetails["payment_methods"] as $method) {
                            $oneMethod          = [];
                            $oneMethod["code"]  = $method->getCode();
                            $oneMethod["title"] = $method->getTitle();
                            $oneMethod["extraInformation"] = "";
                            if(in_array($method->getCode(), ["paypal_standard", "paypal_express"])){
                                $oneMethod["extraInformation"] = __("You will be redirected to the PayPal website.");
                                $config = $this->paypalConfig->setMethod($method->getCode());
                                $locale = $this->localeResolver;
                                $oneMethod["title"]    = "";
                                $oneMethod["link"]     = $config->getPaymentMarkWhatIsPaypalUrl($locale);
                                $oneMethod["imageUrl"] = $config->getPaymentMarkImageUrl($locale->getDefaultLocale());
                            }
                            else
                            if(in_array($method->getCode(), ["paypal_express_bml"])){
                                $oneMethod["extraInformation"] = __("You will be redirected to the PayPal website.");
                                $oneMethod["title"]    = "";
                                $oneMethod["link"]     = "https://www.securecheckout.billmelater.com/paycapture-content/fetch?hash=AU826TU8&content=/bmlweb/ppwpsiw.html";
                                $oneMethod["imageUrl"] = "https://www.paypalobjects.com/webstatic/en_US/i/buttons/ppc-acceptance-medium.png";
                            }
                            else
                            if($method->getCode() == "checkmo"){
                                if($method->getPayableTo())
                                    $extraInformationPrefix = __("Make Check payable to:");
                                else
                                    $extraInformationPrefix = __("Send Check to:");
                                $extraInformation = $this->helper->getConfigData("payment/".$method->getCode()."/mailing_address");
                                if($extraInformation == "")
                                    $extraInformation = __(" xxxxxxx");
                                $oneMethod["extraInformation"] = $extraInformationPrefix.$extraInformation;
                            }
                            else
                            if($method->getCode() == "banktransfer"){
                                $extraInformation = $this->helper->getConfigData("payment/".$method->getCode()."/instructions");
                                if($extraInformation == "")
                                    $extraInformation = __("Bank Details are xxxxxxx");
                                $oneMethod["extraInformation"] = $extraInformation;
                            }
                            else
                            if($method->getCode() == "cashondelivery"){
                                $extraInformation = $this->helper->getConfigData("payment/".$method->getCode()."/instructions");
                                if($extraInformation == "")
                                    $extraInformation = __("Pay at the time of delivery");
                                $oneMethod["extraInformation"] = $extraInformation;
                            }
                            else
                            if (in_array($method->getCode(), ["webkul_stripe", "authorizenet"])) {
                                $allowedCc            = [];
                                $allowedCcTypesString = $method->getConfigData("cctypes");
                                $allowedCcTypes       = explode(",", $allowedCcTypesString);
                                $ccTypes              = $this->ccType->toOptionArray();
                                $types                = [];
                                foreach ($ccTypes as $data) {
                                    if (isset($data["value"]) && isset($data["label"]))
                                        $types[$data["value"]] = $data["label"];
                                }
                                foreach ($allowedCcTypes as $value) {
                                    $eachAllowedCc         = [];
                                    $eachAllowedCc["code"] = $value;
                                    $eachAllowedCc["name"] = $types[$value];
                                    $allowedCc[]           = $eachAllowedCc;
                                }
                                $extraInformation          = $allowedCc;
                                $oneMethod["extraInformation"] = $extraInformation;
                            }
                            $returnArray["paymentMethods"][] = $oneMethod;
                        }
                        $returnArray["success"] = true;
                        $this->emulate->stopEnvironmentEmulation($environment);
                        $this->helper->log($returnArray, "logResponse", $wholeData);
                        return $this->getJsonResponse($returnArray);
                    } else {
                        return $this->getJsonResponse($returnArray, 401, $authData["token"]);
                    }
                } else {
                    $returnArray["message"]      = __("Invalid Request");
                    $this->helper->log($returnArray, "logResponse", $wholeData);
                    return $this->getJsonResponse($returnArray);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $returnArray["message"] = $e->getMessage();
                $this->helper->printLog($returnArray, 1);
                return $this->getJsonResponse(
                    $returnArray
                );    
            } catch(\Exception $e)   {
                $returnArray["message"] = $e->getMessage();
                $this->helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

    }