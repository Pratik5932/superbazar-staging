<?php
namespace MageArray\StorePickup\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_ENABLE = 'storepickup/general/enable';
    const XML_PATH_PICKUPDATE = 'storepickup/general/pickupdate';


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \MageArray\StorePickup\Model\StoreFactory $storeFactory
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->regionFactory = $regionFactory;
        $this->_storeFactory = $storeFactory;
        parent::__construct($context);
    }

    public function isEnabled($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function disablePickupDate($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_PICKUPDATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getWorkingHour($storeId)
    {
        $store = $this->_storeFactory->create()->load($storeId);
        $workingHours = $store->getWorkingHours();
        return $workingHours;
    }

    public function getStoreAddress($storeId)
    {
        $storeDetail = $this->_storeFactory->create()->load($storeId);
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
        $storename = $this->getStoreName($storeDetail->getStoreName());
        $storeArr["firstname"] = $storename["firstname"];
        $storeArr["lastname"] = $storename["lastname"];
        $storeArr["phone_number"] = $storeDetail->getPhoneNumber();
        $storeArr["address"] = $storeDetail->getAddress();
        $storeArr["city"] = $storeDetail->getCity();
        $storeArr["zipcode"] = $storeDetail->getZipcode();
        $storeArr["country"] = $storeDetail->getCountry();
        $storeArr["state"] = $storeDetail->getState();
        $storeArr["region_id"] = $regionId;
        $storeArr["region_code"] = $regionCode;
        return $storeArr;
    }

    public function getStoreName($store)
    {
        $name = [];
        $storeName = explode(" ", $store);
        $count = count($storeName);
        if ($count == 1) {
            $name['firstname'] = $storeName[0];
            $name['lastname'] = $storeName[0];
        }

        if ($count == 2) {
            $name['firstname'] = $storeName[0];
            $name['lastname'] = $storeName[1];
        }

        if ($count > 2) {

            $name['firstname'] = $storeName[0];
            array_shift($storeName);
            $name['lastname'] = implode(" ", $storeName);
        }
        return $name;
    }
}
