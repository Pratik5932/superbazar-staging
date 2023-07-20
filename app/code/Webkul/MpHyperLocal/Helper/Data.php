<?php
/**
* Webkul Software.
*
* @category  Webkul
* @package   Webkul_MpHyperLocal
* @author    Webkul
* @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
* @license   https://store.webkul.com/license.html
*/
namespace Webkul\MpHyperLocal\Helper;

use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Catalog\Model\ProductFactory;
use Webkul\MpHyperLocal\Model\ShipAreaFactory;
use Magento\Framework\HTTP\Client\Curl;
use Webkul\Marketplace\Model\ProductFactory as MpProductFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
    * @var CookieManagerInterface
    */
    protected $cookieManager;

    /**
    * @var \Webkul\MpHyperLocal\Model\ShipAreaFactory
    */
    protected $shipArea;

    /**
    * @var \Webkul\Marketplace\Model\ProductFactory
    */
    protected $mpProduct;

    /**
    * @var JsonHelper
    */
    protected $jsonHelper;

    protected $useragent;
    protected $httpHeader;

    /**
    * @param \Magento\Framework\App\Helper\Context $context
    * @param CookieManagerInterface $cookieManager
    */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CookieManagerInterface $cookieManager,
        ProductFactory $productFactory,
        ShipAreaFactory $shipArea,
        MpProductFactory $mpProduct,
        Curl $curl,
        JsonHelper $jsonHelper,
        \Superbazaar\CustomWork\Model\UserAgentFactory $useragent,
        \Magento\Framework\HTTP\Header $httpHeader
    ) {
        $this->cookieManager = $cookieManager;
        $this->productFactory = $productFactory;
        $this->shipArea = $shipArea;
        $this->mpProduct = $mpProduct;
        $this->curl = $curl;
        $this->jsonHelper = $jsonHelper;
        $this->useragent = $useragent;
        $this->httpHeader = $httpHeader;
        parent::__construct($context);
    }

    /**
    * Get Confi Detail
    *
    * @return array
    */
    public function getConfiDetail()
    {
        $config = [
            'enable' => $this->scopeConfig->getValue('mphyperlocal/general_settings/enable')
        ];
        return $config;
    }

    /**
    * Get Formatted Time
    *
    * @return Time
    */
    public function getFormettedTime($time)
    {
        return date('h:i:s a', strtotime($time));
    }

    /**
    * getSavedAddress
    *
    * @return bool | array
    */
    public function getSavedAddress()
    {
        $enable = $this->scopeConfig->getValue('mphyperlocal/general_settings/enable');
        if (!$enable) {
            return [];
        }
        $uniqueId = md5(
            ($_SERVER['HTTP_USER_AGENT'] ?? "").
            ($_SERVER['LOCAL_ADDR'] ?? "").
            ($_SERVER['LOCAL_PORT'] ?? "").
            ($_SERVER['REMOTE_ADDR'] ?? "")
        );
        $userAgentCollection = $this->useragent
        ->create()
        ->getCollection()
        ->addFieldToFilter("useragent", ["eq" => $uniqueId]);
        if ($userAgentCollection->getSize()) {
            $zipcode = $userAgentCollection
            ->setPageSize(1)
            ->getFirstItem()
            ->getZipcode();
            if (in_array($zipcode , explode(",", $this->getAllAvailablePostcode()))) {
                $location = [];
                $location["zipcode"] = $zipcode;
                $location["address"] = $zipcode;
                $location['latitude'] = '';
                $location['longitude'] = '';
                $location['city'] = '';
                $location['state'] = '';
                $location['country'] = '';

                return $location;
            }
        }

        $address = $this->cookieManager->getCookie('hyper_local') ?? "[]";

        $setAddress = $this->jsonHelper->jsonDecode($address);
        $location = [];
        if (!empty($setAddress)) {
            $location['latitude'] = $setAddress['lat'];
            $location['longitude'] = $setAddress['lng'];
            $location['address'] = $setAddress['address'];
            $location['city'] = $setAddress['city'] ?? '';
            $location['state'] = $setAddress['state'] ?? '';
            $location['country'] = $setAddress['country'] ?? '';
            $location['zipcode'] = $setAddress['zipcode'] ?? '';
        }
        return $location;
    }

    /**
    * getDistanceFromTwoPoints
    * @param string $from
    * @param string $to
    * @return float $d
    */
    public function getDistanceFromTwoPoints($from, $to, $radiousUnit)
    {
        $R = 6371; // km
        $dLat = ($from['latitude'] - $to['latitude']) * M_PI / 180;
        $dLon = ($from['longitude'] - $to['longitude']) * M_PI / 180;
        $lat1 = $to['latitude'] * M_PI / 180;
        $lat2 = $from['latitude'] * M_PI / 180;

        $a = sin($dLat/2) * sin($dLat/2) + sin($dLon/2) * sin($dLon/2) * cos($lat1) * cos($lat2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $d = $R * $c;
        if ($radiousUnit == 'mile') {
            $m = $d * 0.621371; //for milles
            return $m;
        }
        return $d;
    }

    /**
    * getSellersProducts
    * @param array $sellerIds
    * @return array
    */
    public function getNearestProducts($sellerIds)
    {
        $adminProList = [];
        $mpProColl = $this->mpProduct->create()->getCollection()->addFieldToFilter('seller_id', ['in' => $sellerIds])
        ->getColumnValues('mageproduct_id');
        $null = 0;
       if (in_array($null, $sellerIds, true)) {
            $adminProList = $this->getAdminProducts();
       }
        $allowedProList = array_merge($mpProColl, $adminProList);
        return empty($allowedProList) ? [0] : $allowedProList;
    }

    /**
    * getAdminProducts
    * @return array
    */
    public function getAdminProducts()
    {
        $sellerProList = $this->mpProduct->create()->getCollection()->getColumnValues('mageproduct_id');
        $collection = $this->productFactory->create()->getCollection();
        # print_r($sellerProList);exit;
        if (!empty($sellerProList)) {
            $collection->addFieldToFilter('entity_id', ['nin' => $sellerProList]);
        }
        $adminProList = $collection->getColumnValues('entity_id');
        return empty($adminProList) ? [0] : $adminProList;
    }
    /**
    * getNearestSellers
    * @return array
    */
    public function getNearestSellers()
    {
        $collectionFilterOption = $this->scopeConfig->getValue('mphyperlocal/general_settings/show_collection');
        $allowedAddress = $this->getAllowedAddress();
        //  if (!is_array($allowedAddress['zipcode'] ?? false )) continue;
        
        if ($collectionFilterOption == 'zipcode') {
            $sellerIds = [];
            if(isset($allowedAddress['zipcode'])){

                $collection = $this->shipArea->create()
                ->getCollection()
                ->addFieldToFilter('address_type', 'postcode')
                ->addFieldToFilter('postcode', $allowedAddress['zipcode']);

                foreach ($collection as $shipArea) {
                    // if (!is_array($shipArea)) continue;
                    $sellerIds[] = $shipArea->getSellerId() ?? 0;
                }
            }
                return $sellerIds;
        }
        $collection = $this->shipArea->create()
        ->getCollection();

        $sellerIds = [];
        $collectionArray = [];
        foreach ($allowedAddress as $key => $value) {
            if ($value) {
                $collectionArray[] = $this->shipArea->create()
                ->getCollection()
                ->addFieldToFilter('address_type', $key)
                ->addFieldToFilter('address', ['like' => '%'.$value.'%'])->getSelect();
            }
        }
        if (count($collectionArray) == 3) {
            $collection->getSelect()->reset();
            $collection->getSelect()->union([$collectionArray[0], $collectionArray[1], $collectionArray[2]]);
        } elseif (count($collectionArray) == 2) {
            $collection->getSelect()->reset();
            $collection->getSelect()->union([$collectionArray[0], $collectionArray[1]]);
        } elseif (count($collectionArray) == 1) {
            $collection->getSelect()->reset();
            $collection->getSelect()->union([$collectionArray[0]]);
        }
        foreach ($collection as $shipArea) {
            if ($collectionFilterOption == 'radius') {
                $isInRadious = $this->isInRadious($shipArea->getData());
                if ($isInRadious) {
                    $sellerIds[] = $shipArea->getSellerId();
                }
            } else {
                $sellerIds[] = $shipArea->getSellerId();
            }
        }
        return array_unique($sellerIds);
    }

    /**
    * 
    *
    * @return array
    */
    public function getAllowedAddress()
    {
        $address = $this->getSavedAddress();
        if ($address) {
            return [
                'city'    => $address['city'],
                'state'   => $address['state'],
                'country' => $address['country'],
                'zipcode' => $address['zipcode'] ?? $address['address']
            ];
        }
    }

    /**
    * isInRadious
    * @param \Webkul\MpHyperLocal\Model\ShipArea $shipArea
    * @return bool
    */
    public function isInRadious($shipArea)
    {
        $distance = 0;
        $radious = $this->scopeConfig->getValue('mphyperlocal/general_settings/radious');
        $radiousUnit = $this->scopeConfig->getValue('mphyperlocal/general_settings/radious_unit');
        $to['latitude'] = $shipArea['latitude'];
        $to['longitude'] = $shipArea['longitude'];
        $savedAddress = $this->getSavedAddress();
        if ($savedAddress) {
            $distance = $this->getDistanceFromTwoPoints($savedAddress, $to, $radiousUnit);
        }
        return $radious >= $distance;
    }

    /**
    * isSellerAvilableInSavedLocation
    * @param int $sellerId
    * @return boolean
    */
    public function isSellerAvilableInSavedLocation($sellerId)
    {
        $sellerlist = $this->getNearestSellers();
        return in_array($sellerId, $sellerlist);
    }

    public function getCollectionFilter() {
        return $this->scopeConfig->getValue('mphyperlocal/general_settings/show_collection');
    }

    public function getAllAvailablePostcode() {
        $postCodes = [];
        $collection = $this->shipArea->create()
        ->getCollection()
        ->addFieldToSelect('postcode')
        ->addFieldToFilter('address_type', 'postcode');
        $postCodes = $collection->getColumnValues('postcode');
        sort($postCodes);
        $postCodes = implode(',', $postCodes);
        return $postCodes;
    }

    public function getSellerByPostcode($pc) {
        //$postCodes = [];
        //ho '1';            exit;
        $collection = $this->shipArea->create()->getCollection()->addFieldToSelect('postcode')->addFieldToSelect('seller_id')->addFieldToFilter('postcode', $pc);

        $sid = $collection->getColumnValues('seller_id');
        //sort($postCodes);
        //PostCodes = implode(',', $postCodes);
        //PostCodes = implode(',', $sid);
        //Return $postCodes;
        if(is_array($sid)){
            if(count($sid)>0){
                return $sid[0];
            } else { return false;}
        } else {
            return false;
        }
    }

    /**
     * Get Product Collection Filter Type from HyperLocal Configuration
     *
     * @return string
     */
    public function getFilterCollectionType()
    {
        return 'radius';
    }

    /**
     * Get Google API Key from HyperLocal Configuration
     *
     * @return string
     */
    public function getGoogleApiKey()
    {
        $googleApiKey = $this->getHyperLocalConfig('google_api_key');
        return $googleApiKey;
    }

    /**
     * Get Field Value for HyperLocal Configuration
     */
    public function getHyperLocalConfig($field)
    {
        return $this->scopeConfig->getValue('mphyperlocal/general_settings/'.$field);
    }

}
