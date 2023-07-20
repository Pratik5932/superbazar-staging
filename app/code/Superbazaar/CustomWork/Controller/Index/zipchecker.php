<?php

namespace Superbazaar\CustomWork\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 * @package Aitrillion\DataSync\Controller\Index
 */
class zipchecker extends \Magento\Framework\App\Action\Action {

    /** 
     *@var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_jsonResultFactory;


    /**
     * Index constructor.
     * @param Context $context
     * @param SettingsFactory $modelSettingsFactory
     */
    public function __construct(Context $context, \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory) {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute() {
        $params = $this->getRequest()->getParams();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $custId = $customerSession->getCustomer()->getId();

        $address = $objectManager->get('Webkul\MpHyperLocal\Helper\Data')->getSavedAddress();
        $profilePostCode = $address ? $address['address'] :'';
        if($custId){
            $cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
            $items = $cart->getQuote()->getAllVisibleItems();
            $zipcode = $params['zipcode'];
            $resultJson = $this->resultJsonFactory->create();
            if(count($items) > 0){
                foreach ($items as $item) {
                    $data = explode('-', $item->getSku());
                    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
                    $attrName = $product->getAttributeText('store_location');
                    /*$psku = $product->getSku();
                    $skus = explode("-", $psku);*/
                        $storeLocation =  $product->getAttributeText('store_location');
        
                        $postCodes = [];

                        $collection = $objectManager->create('Webkul\MpHyperLocal\Model\ShipAreaFactory')->create()
                        ->getCollection()
                        ->addFieldToSelect('seller_id')
                        ->addFieldToFilter('address_type', 'postcode')          
                        ->addFieldToFilter('postcode', $storeLocation);
                        $sellerId = $collection->getColumnValues('seller_id');
                        if($storeLocation == "3024" ){
                            $sellerId = array("6637");
                        }
                        $collectionpostcode = $objectManager->create('Webkul\MpHyperLocal\Model\ShipAreaFactory')->create()
                        ->getCollection()
                        ->addFieldToSelect('postcode')
                        ->addFieldToFilter('address_type', 'postcode')          
                        ->addFieldToFilter('seller_id', $sellerId);
                        $postCodes = $collectionpostcode->getColumnValues('postcode');
                        //$postCodes[] = $loca;
                        sort($postCodes);
                        // $postCodes = implode(',', $postCodes);
                        //print_r($profilePostCode);
                    if (!in_array($profilePostCode, $postCodes)) {
                        return $resultJson->setData(['message' => "Profile post code is ".$profilePostCode." and some of your items in cart is for other postcode. Please clear the shopping cart and shop again.",'msg' => 'error', 'value' => 3]);
                    }
                    
                } 
                return $resultJson->setData(['msg' => 'success']);
            }
        } 
    }   
}