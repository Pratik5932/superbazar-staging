<?php
namespace Webkul\MobikulMpHyperLocal\Controller\Checkout;

class ShippingMethods extends \Webkul\MobikulApi\Controller\Checkout\ShippingMethods
{
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            try {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $scopeConfig = $objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
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
                
                $hyperlocalHelper = $objectManager->create(\Webkul\MpHyperLocal\Helper\Data::class);
                
                // if ($hyperlocalHelper->isEnabled()) {
                    if (!isset($this->wholeData["address"])) {
                        $this->returnArray["otherError"] = "emptyHyperlocalAddress";
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __("Please select your address.")
                        );
                    } else {
                        $quote = new \Magento\Framework\DataObject();
                        $addressConfig = $objectManager->get(\Magento\Customer\Model\Address\Config::class);
                        $countryDirectory = $objectManager->get(\Magento\Directory\Model\Country::class);

                        if ($this->customerId != 0) {
                            $quote = $this->helper->getCustomerQuote($this->customerId);
                            $this->quoteId = $quote->getEntityId();
                        }
                        if ($this->quoteId != 0) {
                            $quote = $this->quoteFactory->create()->setStoreId($this->storeId)->load($this->quoteId);
                        }
                        
                        $shipAddress = [
                            "address" => $this->wholeData["address"] ?? "",
                            "latitude" => $this->wholeData["latitude"] ?? "",
                            "longitude" => $this->wholeData["longitude"] ?? "",
                            "city" => $this->wholeData["city"] ?? "",
                            "state" => $this->wholeData["state"] ?? "",
                            "country" => $this->wholeData["country"] ?? "",
                        ];                    

                        try {
                            $quoteShipAdd = $quote->getShippingAddress();
                            if (isset($this->shippingData["addressId"]) && $this->shippingData["addressId"] != 0) {
                                $customerAddress = $objectManager->create(\Magento\Customer\Model\Address::class);
                                $quoteShipAdd = $customerAddress->load($this->shippingData["addressId"]);
                                $shippingAddress = implode(" ",$quoteShipAdd->getStreet())." ".$quoteShipAdd->getCity()." ".
                                $quoteShipAdd->getRegion()." ".$quoteShipAdd->getCountryModel()->getName();
                                $shippingZipcode = $quoteShipAdd->getPostcode();
                            } else {
                                $region = $objectManager->create(\Magento\Directory\Model\Region::class)->load($this->shippingData['newAddress']['region_id']);
                                $regionName = $region->getName();
                                $country = $countryDirectory->loadByCode($this->shippingData['newAddress']['country_id']);
    
                                $shippingAddress = implode(" ",$this->shippingData['newAddress']['street'])." ".$this->shippingData['newAddress']['city']." ".$regionName." ".$country->getName();
                                $shippingZipcode = $this->shippingData['newAddress']['postcode'];
                            }
                        } catch (\Exception $e) {
                            if (isset($this->shippingData["addressId"]) && $this->shippingData["addressId"] == 0 && empty($this->shippingData['newAddress'])) {
                                throw new \Exception(__("Please select an existing address or fill a new address properly."));
                            }
                            throw new \Exception(__("Issue occurred from customer shipping address."));
                        }

                        $radiusShippingAddress = $shippingAddress;
                        
                        $hyperlocalAddress = $shipAddress["city"]." ".$shipAddress["state"]." ".$shipAddress["country"]." ".$shipAddress["address"];
                        $hyperlocalAddress = explode(" ",strtolower($hyperlocalAddress));
                        
                        $shippingAddress = explode(" ",strtolower(str_replace(","," ",$shippingAddress)));
                        $collectionFilterOption = $scopeConfig->getValue('mphyperlocal/general_settings/show_collection');
                        if ($collectionFilterOption == "zipcode") {
                            if ($shippingZipcode != $this->wholeData["address"]) {
                                throw new \Magento\Framework\Exception\LocalizedException(
                                    __("Shipping Postcode entered at home page for shopping was %1 whereas the shipping address selected now at checkout is of post code %2. Please update the correct shipping address to proceed with checkout.", $shippingZipcode, $this->wholeData["address"])
                                );
                            }
                        } else {
                            foreach($hyperlocalAddress as $single) {
                                if (in_array($single, $shippingAddress) === false) {
                                    throw new \Magento\Framework\Exception\LocalizedException(
                                        __('Shipping address is not same as address selected.')
                                    );
                                }
                            }
                        }
                        $filterType = $hyperlocalHelper->getFilterCollectionType();
                        if ($filterType == "radius") {
                            $scopeConfig = $objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
                            $googleApiKey = $hyperlocalHelper->getGoogleApiKey();
                            $radius = $scopeConfig->getValue('mphyperlocal/general_settings/radious');
                            $radiusUnit = $scopeConfig->getValue('mphyperlocal/general_settings/radious_unit');
                            
                            foreach($quote->getItemsCollection() as $item) {
                                $productId = $item->getProduct()->getId();
                                $mpProduct = $objectManager->create(\Webkul\Marketplace\Model\Product::class)->load($productId,"mageproduct_id");
                                $sellerId = $mpProduct->getSellerId();
                                $sellerCollection = $objectManager->create(\Webkul\Marketplace\Model\Seller::class)->getCollection()
                                                        ->addFieldToFilter("seller_id",$sellerId);
                                $sellerOriginAddress = false;
                                if ($this->storeId) {
                                    $sellerData = $sellerCollection->addFieldToFilter("store_id",$this->storeId);
                                    if ($sellerData->getSize()) {
                                        $seller = $sellerData->getLastItem();
                                        if($seller->getOriginAddress()) {
                                            $sellerOriginAddress = true;
                                            $sellerHyperlocal = [
                                                'address' => $seller->getOriginAddress(),
                                                'latitude' => $seller->getLatitude(),
                                                'longitude' => $seller->getLongitude()
                                            ];
                                        }
                                    }
                                }
                                if (empty($this->storeId) || $sellerOriginAddress == false) {
                                    $seller = $objectManager->create(\Webkul\Marketplace\Model\Seller::class)
                                                            ->getCollection()
                                                            ->addFieldToFilter("seller_id",$sellerId)
                                                            ->addFieldToFilter("store_id",0)
                                                            ->getLastItem();
                                    $sellerHyperlocal = [
                                        'address' => $seller->getOriginAddress(),
                                        'latitude' => $seller->getLatitude(),
                                        'longitude' => $seller->getLongitude()
                                    ];
                                }
                                $fileDriver = $objectManager->create(\Magento\Framework\Filesystem\Driver\File::class);
                                $address = str_replace(' ', '+', $radiusShippingAddress);
                                $address = str_replace('++', '+', $address);   
//                                     $url = "https://maps.google.com/maps/api/geocode/json?key=".$googleApiKey."&address=".$radiusShippingAddress;
                                $curl = $objectManager->create(\Magento\Framework\HTTP\Client\Curl::class);
                                
                                $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$address.'&sensor=false&key='.$googleApiKey;
                                $curl->get($url);
                                $response = json_decode($curl->getBody());
                                $response = json_decode(json_encode($response), true);
                                $location = [];
                                if (count($response['results']) > 0) {
                                    $location = $response['results'][0]['geometry']['location'];
                                }
                                $from['latitude'] = $location['lat'] ?? 0;
                                $from['longitude'] = $location['lng'] ?? 0;
                                $distance = $hyperlocalHelper->getDistanceFromTwoPoints($from, $sellerHyperlocal, $radiusUnit);
                                $sellerRadius = $seller->getRadius();
                                if ($sellerRadius != 0) {
                                    $radius = $sellerRadius;
                                }
                                if ($collectionFilterOption != "zipcode") {
                                    if (($distance > $radius)) {
                                        throw new \Magento\Framework\Exception\LocalizedException(
                                            __('Shipping Address is outside delivery area of seller.')
                                        );
                                    }
                                }
                            }
                        }
                    }
                // }
            } catch (\Exception $e) {
                throw $e;
            }
        } else {
            throw new \Exception(__("Invalid Request"));
        }
    }
}