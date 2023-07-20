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
* Class ShippingMethods
* To get available shipping methods at checkout.
*/
class ShippingMethods extends AbstractCheckout
{
    const TIME_OF_DAY_IN_SECONDS = 86400;
    /**
    * Execute Function for ShippingPaymentMethodInfo Class
    *
    * @return void
    */

    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $store = $this->store;
            $baseCurrency = $store->getBaseCurrencyCode();
            $currency = $this->wholeData["currency"] ?? $baseCurrency;
            $store->setCurrentCurrencyCode($currency);
            $quote = new \Magento\Framework\DataObject();
            if ($this->customerId != 0) {
                $quote = $this->helper->getCustomerQuote($this->customerId);
                $this->quoteId = $quote->getEntityId();
            }
            if ($this->quoteId != 0) {
                $quote = $this->quoteFactory->create()->setStoreId($this->storeId)->load($this->quoteId);
            }
            if ($quote->isVirtual()) {
                $totals = $quote->getBillingAddress()->getTotals();
            } else {
                $totals = $quote->getShippingAddress()->getTotals();
            }
            if (isset($totals["grand_total"])) {
                $grandtotal = $totals["grand_total"];
                $this->returnArray["cartTotal"] = $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($grandtotal->getValue())
                );
            } else {
                $this->returnArray["cartTotal"] = 0;
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
            $useForShipping = 0;
            if (!empty($this->shippingData) &&
                (
                    isset($this->shippingData["addressId"]) &&
                    ($this->shippingData["addressId"] > 0 || !empty($this->shippingData["newAddress"]))
                )
                ) {
                    $saveInAddressBook = 0;
                    $this->getShippingMethods($quote);
            } else {
                if ($this->quoteId != "") {
                    $shippingAddressInterface = $this->addressInterface->setCountryId(
                        $quote->getShippingAddress()->getCountry()
                    )
                    ->setPostcode(null)
                    ->setRegionId(null);
                    $availableMethods = $this->shippingMethodManagement
                    ->estimateByExtendedAddress($this->quoteId, $shippingAddressInterface);
                    $isSelected = false;
                    if (!is_array($availableMethods) && $availableMethods->getSize() == 1) {
                        $isSelected = true;
                    }
                    foreach ($availableMethods as $eachMethod) {
                        $oneShipping = [];
                        $oneMethod["isSelected"] = $isSelected;
                        $oneMethod["code"] = $eachMethod->getCarrierCode();
                        $oneMethod["label"] = $eachMethod->getMethodTitle();
                        $oneMethod["price"] = $this->helperCatalog->stripTags(
                            $this->priceHelper->currency((float)$eachMethod->getAmount())
                        );
                        $oneShipping["title"] = $eachMethod->getCarrierTitle();
                        $oneShipping["method"][] = $oneMethod;
                        $this->returnArray["shippingMethods"][] = $oneShipping;
                    }
                    $totals = [];
                    $this->returnArray["success"] = true;
                    return $this->getJsonResponse($this->returnArray);
                } else {
                    $this->returnArray["message"] = __("Invalid Quote Id");
                    return $this->getJsonResponse($this->returnArray);
                }
            }
            $this->returnArray["success"] = true;

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $bssHelper = $objectManager->get("Bss\OrderDeliveryDate\Helper\Data");
            #mail("er.bharatmali@gmail.com","delivery-info-test55",$bssHelper->isEnabled());

            if ($bssHelper->isEnabled()) {
                $this->returnArray['bss_delivery_timeslot'] = $this->getTimeSlot();
                $day_off = $this->getDayOff();
                #mail("er.bharatmali@gmail.com","delivery-info-test",$day_off);
                #echo $day_off;exit;
                $block_out_holidays = $bssHelper
                ->returnClassSerialize()
                ->unserialize($this->getBlockHoliday());

                $current_time = (int) $bssHelper->getStoreTimestamp();
                $cut_off_time_convert = $this->getCutOffTime();




                $process_time = $bssHelper->getProcessingTime();
                // Check if over cut of time in day then + 1 processing day

                # echo $current_time > $cut_off_time_convert;exit;
                if ($cut_off_time_convert &&
                $current_time > $cut_off_time_convert &&
                !$this->isProcessingDayDisabled()) {

                    $process_time++;
                }
                #echo  $this->bssHelper->getStoreTimezone();exit;
                //  $block_out_holidays = !empty($block_out_holidays) ? json_encode($block_out_holidays) : '';
                $block_out_holidays = implode(",",$block_out_holidays);
                               // mail("er.bharatmali@gmail.com","test",$block_out_holidays);

                $block_out_holidays = !empty($block_out_holidays) ? ($block_out_holidays) : '';
                $this->returnArray['bss_shipping_comment'] = (boolean) $bssHelper->isShowShippingComment();
                $this->returnArray['bss_delivery_process_time'] = $process_time;
                $this->returnArray['bss_delivery_block_out_holidays'] = $block_out_holidays;
                $this->returnArray['bss_delivery_day_off'] = $day_off;
                $this->returnArray['bss_delivery_date_fomat'] = $bssHelper->getDateFormat();
                $this->returnArray['bss_delivery_current_time'] = $current_time;
                $this->returnArray['bss_delivery_time_zone'] = $bssHelper->getTimezoneOffsetSeconds();
                $this->returnArray['as_processing_days'] = $bssHelper->isAsProcessingDays();
                $this->returnArray['store_time_zone'] = $bssHelper->getStoreTimezone();
                if ($bssHelper->getIcon()) {
                    $output['bss_delivery_icon'] = $bssHelper->getIcon();
                }
                $this->returnArray['date_field_required'] = $bssHelper->isFieldRequired('required_date');
                $this->returnArray['times_field_required'] = $bssHelper->isFieldRequired('required_timeslot');
                $this->returnArray['comment_field_required'] =$bssHelper->isFieldRequired('required_comment');
                $this->returnArray['on_which_page'] = $bssHelper->getDisplayAt();
                $this->returnArray['action_payment_save'] = $bssHelper->getPaymentSaveAction();
                $this->returnArray['today_date'] = $bssHelper->getDateToday();
                $this->returnArray['min_date'] = $this->getMindate($day_off, $block_out_holidays, $process_time, $current_time);
                $this->returnArray['bss_format_date'] = $bssHelper->formatDate();
                if(!$cut_off_time_convert)
                $this->returnArray['hide_calendar'] = true; 

            }
            # mail("er.bharatmali@gmail.com","delivery-info",'<pre>'.print_r($this->returnArray, true).'</pre>');
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"] = $e->getMessage();
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = $e->getMessage();
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    public function getDayOff()
    {
        # return;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $day_off =  $objectManager->get("Magento\Framework\App\Config\ScopeConfigInterface")->getValue(
            'orderdeliverydate/general/cut_off_time',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($day_off === null) {
            return false;
        }  
        $zipCode = $_GET['address'];

        # echo $day_off;exit;
        $response = [];
        if ($day_off) {
            $day_off_arr = $objectManager->get('Magento\Framework\Serialize\Serializer\Serialize')->unserialize($day_off);
            if (is_array($day_off_arr)) {
                foreach ($day_off_arr as $dayoff) {

                    $dayoffa = explode(",", $dayoff["name"]);
                    if(is_array($dayoffa) && in_array($zipCode,$dayoffa)){
                        // if(isset($dayoff["name"]) && $dayoff["name"] ==$this->getSellerPostcode()){
                        if(isset($dayoff['deliverydate_day_off'])){
                            foreach($dayoff['deliverydate_day_off'] as $date){
                                $response[] = $date; 
                            }
                        }

                    }
                }
            }
        }
        return implode(",",$response);


    }
    public function getBlockHoliday()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $block_out_holidays =  $objectManager->get("Magento\Framework\App\Config\ScopeConfigInterface")->getValue(
            'orderdeliverydate/general/block_out_holidays',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $zipCode = $_GET['address'];

        $response = [];
        if ($block_out_holidays) {
            $block_out_holidays_arr = $objectManager->get('Magento\Framework\Serialize\Serializer\Serialize')->unserialize($block_out_holidays);
            if ($block_out_holidays_arr) {

                foreach ($block_out_holidays_arr as $holidays) {
                    $holidaysa = explode(",", $holidays["content"]);
                    if(is_array($holidaysa) && in_array($zipCode,$holidaysa)){

                        // if(isset($holidays["content"]) && $holidays["content"] ==$this->getSellerPostcode()){
                        $newDate = date("Y-m-d", strtotime($holidays['date']));
                        $response[] = $newDate;
                    }
                }
            }
        }
        #print_R($response);exit;
        return $objectManager->get('Magento\Framework\Serialize\Serializer\Serialize')->serialize($response);
    }
    public function getCutOffTime()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cut_off_time =  $objectManager->get("Magento\Framework\App\Config\ScopeConfigInterface")->getValue(
            'orderdeliverydate/general/cut_off_time',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $zipCode = $_GET['address'];

        $cut_off_time_convert = "";
        if ($cut_off_time) {
            $cut_off_time_arr = $objectManager->get('Magento\Framework\Serialize\Serializer\Serialize')->unserialize($cut_off_time);
            if ($cut_off_time_arr) {
                foreach ($cut_off_time_arr as $cut_off) {
                    $cut_offa = explode(",", $cut_off["name"]);
                    if(is_array($cut_offa) && in_array($zipCode,$cut_offa)){
                        //$newDate = date("Y-m-d", strtotime($cut_off['cut_off_time']));
                        $cutOffDate = $objectManager->get("Magento\Framework\Stdlib\DateTime\TimezoneInterface")->date()->format('Y-m-d'). ' ' .$cut_off['cut_off_time'];

                        # echo $cutOffDate;exit;
                        $cut_off_time_convert = strtotime($cutOffDate);
                        # echo $cut_off_time_convert;exit;

                    }
                }
            }
        }
        return $cut_off_time_convert;

    }
    public function getTimeSlot()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $time_slots = $objectManager->get("Magento\Framework\App\Config\ScopeConfigInterface")->getValue(
            'orderdeliverydate/general/time_slots',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );   
        if ($time_slots) {
            $time_slot_arr = $objectManager->get("Magento\Framework\Serialize\Serializer\Serialize")->unserialize($time_slots);
            return $this->getClearTimeSlot($time_slot_arr);
        }
        return [];
    }
    protected function getClearTimeSlot($timeSlotArr)
    {


        $zipCode = $_GET['address'];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $bssHelper = $objectManager->get("Bss\OrderDeliveryDate\Helper\Data");
        #echo $sellerPostcode;exit;

        $result = [];
        $processingDay = $bssHelper->getProcessingTime();
        $now = $bssHelper->getStoreTimestamp();
        if (is_array($timeSlotArr) && !empty($timeSlotArr)) {
            foreach ($timeSlotArr as $timeSlot) {

                $timeSlota = explode(",", $timeSlot["name"]);
                if(is_array($timeSlota) && in_array($zipCode,$timeSlota)){

                    //  if(isset($timeSlot["name"]) && $timeSlot["name"] ==$this->getSellerPostcode()){
                    $timeFrom = $this->convertAMPM($timeSlot['from']);
                    $timeTo = $this->convertAMPM($timeSlot['to']);
                    if (!$timeFrom || !$timeTo) {
                        continue;
                    }
                    $disabled = 0;
                    if ($bssHelper->timeLineCondition($timeFrom, $timeTo, $now) && $processingDay == 0) {
                        $disabled = 1;
                    }
                    $a = $timeSlot['note'] . ' ' . $timeSlot['from'] . ' - ' . $timeSlot['to'];
                    $b = ['value' => $a, 'label' => $a, 'disabled' => $disabled];
                    array_push($result, $b); 
                }

            }
        }
        return $result;
    }
    protected function convertAMPM($strTime)
    {
        $exp = "/^[0-9][0-9]:[0-9][0-9]\s[AM|PM]/i"; // Regex check AM PM time
        if (preg_match($exp, $strTime)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $bssHelper = $objectManager->get("Bss\OrderDeliveryDate\Helper\Data");
            return  $objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->gmtTimestamp(date('Y-m-d') . ' ' . $strTime);
        }
        return false;
    }
    protected function getMindate($day_off, $block_out_holidays, $process_time, $current_time)
    {
        // If exclude processing day = no, then return config processing time
        # echo $process_time;exit;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $bssHelper = $objectManager->get("Bss\OrderDeliveryDate\Helper\Data");
        if ($bssHelper->isAsProcessingDays()) {
            return $process_time;
        }
        // If processing time <= 0, then return config processing time
        if ($process_time <= 0) {
            return $process_time;
        }
        // If day off is empty, then return config processing time
        $dayOffArr = $this->getDayAsArray($day_off);
        $holidays = $this->getDayAsArray($block_out_holidays);
        $timeOfDayInSeconds = self::TIME_OF_DAY_IN_SECONDS;
        $tempProcessTime = $process_time;
        $tempDisabledDays = [];
        if (!empty($dayOffArr)) {
            for ($i = 0; $i < $tempProcessTime; $i++) {
                $nextOfDayInTime = $current_time + $i * $timeOfDayInSeconds;
                $momentDate = date('Y-m-d', $nextOfDayInTime);
                $momentDay = date('w', $nextOfDayInTime);
                if (in_array($momentDay, $dayOffArr)) {
                    $tempProcessTime++;
                    $tempDisabledDays[] = $momentDate;
                }
            }
        }
        if (!empty($holidays)) {
            for ($j = 0; $j < $tempProcessTime; $j++) {
                $nextOfDayInTime = $current_time + $j * $timeOfDayInSeconds;
                $momentDate = date('Y-m-d', $nextOfDayInTime);
                $momentDay = date('w', $nextOfDayInTime);
                if (in_array($momentDay, $holidays) && !in_array($momentDay, $tempDisabledDays)) {
                    $tempProcessTime ++;
                }
            }
        }

        $newTime = $tempProcessTime * $timeOfDayInSeconds + $current_time;

        return [
            'dayOfWeek' => date('w', $newTime),
            'extendedDay' => ($tempProcessTime < 7) ? 0 : ($tempProcessTime - ($tempProcessTime % 7)) / 7
        ];
    }
    private function getDayAsArray($days)
    {
        return is_string($days) ? explode(',', $days) : (is_array($days) ? $days : []);
    }
    public function isProcessingDayDisabled()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $bssHelper = $objectManager->get("Bss\OrderDeliveryDate\Helper\Data");
        if ($bssHelper->isAsProcessingDays()) {
            return false;
        }
        # echo $this->bssHelper->isAsProcessingDays();exit;

        $weekDays = $objectManager->get("Magento\Config\Model\Config\Source\Locale\Weekdays")->toOptionArray();
        $dayOff = explode(',', $bssHelper->getDayOff());
        $disableDayName = [];
        foreach ($weekDays as $weekDay) {
            if (isset($weekDay['value']) &&
            isset($weekDay['label']) &&
            in_array($weekDay['value'], $dayOff)) {
                $disableDayName[] = strtolower($weekDay['label']);
            }
        }
        if (in_array(strtolower($bssHelper->getDayOfWeekName()), $disableDayName)) {
            return true;
        }
        return false;
    }
    /**
    * Function to get shipping methods from shipping Data
    *
    * @param \Magento\Quote\Model\Quote $quote quote
    *
    * @return void
    */
    public function getdeductPrice(){
        $zipCode = $_GET['address'];
        $product_id = "";
        $PostcodeProdctPrice = "";
        $total_deduct_price = 0;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $context = $objectManager->get('Magento\Framework\App\Http\Context');
        $isLoggedIn = $context->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        $itemsCollection = $cart->getQuote()->getItemsCollection()->addFieldToFilter('quote_id', $this->quoteId);
        foreach($itemsCollection->getData() as $item) 
        {
            $product_id .= $item['price'].',';
            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item['product_id']);
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
                        $deduct_price  = $item['price'] - $zipCodePrice;
                        $total_deduct_price += $deduct_price * $item['qty'];
                    }
                }
            }
        }
        return $total_deduct_price;
    }
    public function getShippingMethods($quote)
    {
        if (!$quote->isVirtual()) {
            $shippingData = $this->shippingData;
            if ($shippingData != "") {
                $sameAsBilling = 0;
                $newAddress = [];
                if ($shippingData["newAddress"] != "") {
                    if (!empty($shippingData["newAddress"])) {
                        $newAddress = $shippingData["newAddress"];
                    }
                }
                $addressId = 0;
                if ($shippingData["addressId"] != "") {
                    $addressId = $shippingData["addressId"];
                }
                $saveInAddressBook = 0;
                if (isset($shippingData["newAddress"]["saveInAddressBook"]) &&
                $shippingData["newAddress"]["saveInAddressBook"] != ""
                ) {
                    $saveInAddressBook = $shippingData["newAddress"]["saveInAddressBook"];
                }
                $address = $quote->getShippingAddress();
                $addressForm = $this->customerForm;
                $addressForm->setFormCode("customer_address_edit")->setEntityType("customer_address");
                if ($addressId > 0) {
                    $customerAddress = $this->customerAddress->load($addressId)->getDataModel();
                    if ($customerAddress->getId()) {
                        if ($customerAddress->getCustomerId() != $quote->getCustomerId()) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __("Customer Address is not valid.")
                            );
                        }
                        $address->importCustomerAddressData($customerAddress)->setSaveInAddressBook(0);
                        $addressForm->setEntity($address);
                        $addressErrors = $addressForm->validateData($address->getData());
                        if ($addressErrors !== true) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __(implode(", ", $addressErrors))
                            );
                        }
                    }
                } else {
                    $addressForm->setEntity($address);
                    $addressData = [
                        "fax" => $newAddress["fax"],
                        "city" => $newAddress["city"],
                        "region" => $newAddress["region"],
                        "prefix" => $newAddress["prefix"] ?? "",
                        "suffix" => $newAddress["suffix"] ?? "",
                        "street" => $newAddress["street"],
                        "company" => $newAddress["company"],
                        "lastname" => $newAddress["lastName"],
                        "postcode" => $newAddress["postcode"],
                        "region_id" => $newAddress["region_id"],
                        "firstname" => $newAddress["firstName"],
                        "telephone" => $newAddress["telephone"],
                        "middlename" => $newAddress["middleName"] ?? "",
                        "country_id" => $newAddress["country_id"],
                        "address_title" => ($newAddress["address_title"]) ?? ""
                    ];
                    $addressErrors = $addressForm->validateData($addressData);
                    if ($addressErrors !== true) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __(implode(", ", $addressErrors))
                        );
                    }
                    $addressForm->compactData($addressData);
                    $address->setCustomerAddressId(null);
                    // Additional form data, not fetched by extractData (as it fetches only attributes) /////////
                    $address->setSaveInAddressBook($saveInAddressBook);
                    $address->setSameAsBilling($sameAsBilling);
                }
                $address->setCollectShippingRates(true);
                if (($validateRes = $address->validate()) !== true) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __(implode(", ", $validateRes))
                    );
                }
                if (($validateRes = $address->validate()) !== true) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __(implode(", ", $validateRes))
                    );
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Invalid Shipping data.")
                );
            }
            $quote->collectTotals()->save();
            $quote->getShippingAddress()->collectShippingRates()->save();
            $shippingRateGroups = $quote->getShippingAddress()->getGroupedAllShippingRates();
            $isSelected = false;
            if (count($shippingRateGroups) == 1) {
                $isSelected = true;
            }
            foreach ($shippingRateGroups as $code => $rates) {
                $oneShipping = [];
                $oneShipping["isSelected"] = $isSelected;
                $oneShipping["title"] = $this->helperCatalog->stripTags(
                    $this->helper->getConfigData("carriers/".$code."/title")
                );
                foreach ($rates as $rate) {
                    $oneMethod = [];
                    if ($rate->getErrorMessage()) {
                        $oneMethod["error"] = $rate->getErrorMessage();
                    }
                    $oneMethod["code"] = $rate->getCode();
                    $oneMethod["label"] = $rate->getMethodTitle();
                    $oneMethod["price"] = $this->helperCatalog->stripTags(
                        $this->priceHelper->currency((float)$rate->getPrice())
                    );
                    $oneShipping["method"][] = $oneMethod;
                }
                $this->returnArray["shippingMethods"][] = $oneShipping;
            }
        }
    }

    /**
    * Function to verify Request
    *
    * @return void|json
    */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->shippingData = $this->wholeData["shippingData"] ?? "{}";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->shippingData = $this->jsonHelper->jsonDecode($this->shippingData);
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
