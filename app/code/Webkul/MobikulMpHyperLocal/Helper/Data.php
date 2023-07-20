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
namespace Webkul\MobikulMpHyperLocal\Helper;

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

    public function getNearestSellers()
    {
        $collectionFilterOption = $this->scopeConfig->getValue('mphyperlocal/general_settings/show_collection');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $hyperHelper = $objectManager->create(\Webkul\MpHyperLocal\Helper\Data::class);
        $allowedAddress = $hyperHelper->getAllowedAddress();
        $collection = $this->shipArea->create()
                ->getCollection();
        
        $sellerIds = [];
        $collectionArray = [];
        if (is_array($allowedAddress) || is_object($allowedAddress)) {
            foreach ($allowedAddress as $key => $value) {
                if ($value) {
                    $collectionArray[] = $this->shipArea->create()
                    ->getCollection()
                    ->addFieldToFilter('address_type', $key)
                    ->addFieldToFilter('address', ['like' => '%'.$value.'%'])->getSelect();
                }
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
                $isInRadious = $hyperHelper->isInRadious($shipArea->getData());
                if ($isInRadious) {
                    $sellerIds[] = $shipArea->getSellerId();
                }
            } else {
                $sellerIds[] = $shipArea->getSellerId();
            }
        }
        return array_unique($sellerIds);
    }
}