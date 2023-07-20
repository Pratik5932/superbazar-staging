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

class Checkpincode extends \Webkul\MobikulApi\Controller\ApiController
{
    protected $hyperlocalHelper;
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

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->hyperlocalHelper = $objectManager->create(\Webkul\MpHyperLocal\Helper\Data::class);
        $collectionFilter = $this->hyperlocalHelper->getCollectionFilter();
        $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
        if ($collectionFilter == "zipcode") {
            $ids = $this->hyperlocalHelper->getAllAvailablePostcode();  
            $idsArray = explode(",",$this->hyperlocalHelper->getAllAvailablePostcode()); 
            if(!in_array($this->address,$idsArray)){
                $this->returnArray["success"] = false;
                $this->returnArray["message"] = __('We are currently supplying only in %1,  if your Melbourne Post code not in the list, kindly reach out info@superbazaar.com.au to update it.', $ids);
            } else {
                $this->returnArray["success"] = true;    
            }
        } else {
            $this->returnArray["success"] = true;
        }
        return $this->getJsonResponse($this->returnArray);
}
 

}