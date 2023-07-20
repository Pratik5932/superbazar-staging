<?php

namespace Superbazaar\CustomWork\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 * @package Aitrillion\DataSync\Controller\Index
 */
class Checkproductbyzip extends \Magento\Framework\App\Action\Action {

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
                    if($zipcode != $attrName){
                        return $resultJson->setData(['message' => "Shipping Postcode entered at home page for shopping was ".$profilePostCode." whereas the shipping address selected now at checkout is of post code ".$zipcode.".Please update the correct shipping address to proceed with checkout.",'msg' => 'error', 'value' => 1]);
                    }elseif ($zipcode != $profilePostCode) {
                        return $resultJson->setData(['message' => "Shipping Postcode entered at home page for shopping was ".$profilePostCode." whereas the shipping address selected now at checkout is of post code ".$zipcode.".Please update the correct shipping address to proceed with checkout.",'msg' => 'error', 'value' => 2]);
                    }
                    return $resultJson->setData(['msg' => 'success']);
                } 
            }
        } 
    }   
}