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
namespace Webkul\MobikulMpHyperLocal\Rewrite\Helper;

class Data extends \Webkul\MpHyperLocal\Helper\Data
{
    /**
    * getNearestSellers
    * @return array
    */
    public function getNearestSellers()
    {
        if (strpos($_SERVER['REQUEST_URI'],'mobikulhttp') !== false || strpos($_SERVER['REQUEST_URI'],'mobikulmphttp') !== false || strpos($_SERVER['REQUEST_URI'],'mobikulmphl' !== false)) {
            $collectionFilterOption = $this->scopeConfig->getValue('mphyperlocal/general_settings/show_collection');
            $allowedAddress = $this->getAllowedAddress();
            $address = $_REQUEST['address'] ?? "";
            if ($collectionFilterOption == 'zipcode') {
                $sellerIds = [];
                $collection = $this->shipArea->create()
                ->getCollection()
                ->addFieldToFilter('address_type', 'postcode')
                ->addFieldToFilter('postcode', $address);
                foreach ($collection as $shipArea) {
                    $sellerIds[] = $shipArea->getSellerId() ?? 0;
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
        } else {
            return parent::getNearestSellers();
        }
    }
}
