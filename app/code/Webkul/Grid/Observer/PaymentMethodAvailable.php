<?php

namespace Webkul\Grid\Observer;

use Magento\Framework\Event\ObserverInterface;


class PaymentMethodAvailable implements ObserverInterface
{
    /**
    * payment_method_is_active event handler.
    *
    * @param \Magento\Framework\Event\Observer $observer
    */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
        $state =  $objectManager->get('Magento\Framework\App\State');
        if($state->getAreaCode() =="adminhtml"){
            return;
        }
        $savedAddress = $objectManager->get('\Webkul\MpHyperLocal\Helper\Data')->getSavedAddress();
        $payments =$objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $_appConfigScopeConfigInterface =$objectManager->get('\Magento\Payment\Model\Config')->getActiveMethods();

        $methods = array();
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = $_appConfigScopeConfigInterface
            ->getValue('payment/'.$paymentCode.'/title');
            $methods[$paymentCode] = array(
                'label' => $paymentTitle,
                'value' => $paymentCode
            );
        }
        if(isset($savedAddress['zipcode']) && $savedAddress['zipcode']){
            $paymentmethodModel = $objectManager->create('Webkul\Grid\Model\Grid')->getCollection()
            ->addFieldToFilter('postcode', array('like' => '%'.$savedAddress['zipcode'].'%'))
            ->addFieldToFilter('is_active', array('eq' => '1'));

            #echo $paymentmethodModel->getSize();exit;
            if($paymentmethodModel->getSize()){
                foreach($paymentmethodModel as $data){
                    // $data = $paymentmethodModel->getFirstItem();
                   # print_R($data->getData());exit;
                    // if($data->getEntityId()){
                    #print_r($data->getSelectMethod());exit;
                    if($observer->getEvent()->getMethodInstance()->getCode()==$data->getSelectMethod()){
                        $checkResult = $observer->getEvent()->getResult();
                        $checkResult->setData('is_available', false); //this is disabling the payment method at checkout page
                    }       
                }
                //   }
            }


        }

    }
}