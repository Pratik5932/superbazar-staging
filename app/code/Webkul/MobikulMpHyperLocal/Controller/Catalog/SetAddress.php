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

namespace Webkul\MobikulMpHyperLocal\Controller\Catalog;

use Magento\Framework\App\Action\Context;
use Magento\Cms\Model\Page;

class SetAddress extends \Webkul\MobikulApi\Controller\ApiController
{
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $mobikulHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
       
    )  {
        $this->quoteFactory = $quoteFactory;
        $this->_helper = $mobikulHelper;
        parent::__construct($mobikulHelper, $context,$jsonHelper);
    }

 
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $this->customerToken = $data["customerToken"] ?? "";
        $this->token = $data["token"] ?? "";
        $this->storeId = $data["storeId"] ?? 1;
        $this->quoteId = $data["quoteId"] ?? 0;

        $this->lng = $data["lng"] ?? "";
        $this->lat = $data["lat"] ?? "";
        $this->address = $data["address"] ?? "";
        $this->city = $data["city"] ?? "";
        $this->state = $data["state"] ?? "";
        $this->country = $data["country"] ?? "";


        $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $hyperlocalHelper = $objectManager->create(\Webkul\MpHyperLocal\Helper\Data::class);
        $collectionFilter = $hyperlocalHelper->getCollectionFilter();
        if ($collectionFilter != "zipcode") {
            if ($data && $this->lat != '' && $this->lng != '' && ($this->city != '' || $this->state != '' || $this->country != '')) {
                $quote = new \Magento\Framework\DataObject();
                if ($this->customerId != 0) { 
                    $quote = $this->_helper->getCustomerQuote($this->customerId);
                }
                if ($this->quoteId != 0) {
                    $quote = $this->_helper->getQuoteById($this->quoteId)->setStoreId($this->storeId);
                } 
                $this->quoteFactory->create()->load($quote->getId())->delete();
                $this->returnArray["message"] = __('Address Set');
                $this->returnArray["status"] = 1;
                $this->returnArray["success"] = true;
            
            } else {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $this->returnArray["message"] = __('Fill Correct Address');
                $this->returnArray["status"] = 0;
                $this->returnArray["success"] = false;
                
            }
        } else {
            if ($data && ($this->address != '')) {
                $quote = new \Magento\Framework\DataObject();
                if ($this->customerId != 0) { 
                    $quote = $this->_helper->getCustomerQuote($this->customerId);
                }
                if ($this->quoteId != 0) {
                    $quote = $this->_helper->getQuoteById($this->quoteId)->setStoreId($this->storeId);
                } 
                $this->quoteFactory->create()->load($quote->getId())->delete();
                $this->returnArray["message"] = __('Address Set');
                $this->returnArray["status"] = 1;
                $this->returnArray["success"] = true;
            
            } else {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $this->returnArray["message"] = __('Fill Correct Address');
                $this->returnArray["status"] = 0;
                $this->returnArray["success"] = false;
                
            }
        }
        return $this->getJsonResponse($this->returnArray);
}
 

}