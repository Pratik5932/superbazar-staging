<?php
namespace MageArray\StorePickup\Model\Checkout;

class DefaultConfigProvider
{

    public function __construct(
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \MageArray\StorePickup\Model\StoreFactory $storeFactory,
        \MageArray\StorePickup\Helper\Data $dataHelper
    ) {
        $this->regionFactory = $regionFactory;
        $this->_storeFactory = $storeFactory;
        $this->dataHelper = $dataHelper;
    }

    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, $result)
    {
        $enable = $this->dataHelper->isEnabled();
        $result["storePickup"] = "";
        if ($enable == 1) {
            $storeArr = [];
            $storeColl = $this->_storeFactory->create()->getCollection();
            foreach ($storeColl as $storeDetail) {
                $sId = $storeDetail->getStorepickupId();
                $regionColl = $this->regionFactory->create()->getCollection()
                    ->addFieldToFilter("default_name", $storeDetail->getState());
                $regionId = "";
                $regionCode = "";
                if (count($regionColl->getData()) > 0) {
                    foreach ($regionColl as $region) {
                        $regionId = $region->getRegionId();
                        $regionCode = $region->getCode();
                    }
                }
                $storename = $this->dataHelper->getStoreName($storeDetail->getStoreName());
                $storeArr[$sId]["firstname"] = $storename["firstname"];
                $storeArr[$sId]["lastname"] = $storename["lastname"];
                $storeArr[$sId]["phone_number"] = $storeDetail->getPhoneNumber();
                $storeArr[$sId]["address"] = $storeDetail->getAddress();
                $storeArr[$sId]["city"] = $storeDetail->getCity();
                $storeArr[$sId]["zipcode"] = $storeDetail->getZipcode();
                $storeArr[$sId]["country"] = $storeDetail->getCountry();
                $storeArr[$sId]["state"] = $storeDetail->getState();
                $storeArr[$sId]["region_id"] = $regionId;
                $storeArr[$sId]["region_code"] = $regionCode;
            }
            $result["storePickup"] = $storeArr;
        }
        return $result;
    }
}