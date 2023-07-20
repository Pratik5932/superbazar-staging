<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Wallet
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Wallet\Controller\Wallet;

use Magento\Framework\App\Action\Action;

class Checkemailfortransfer extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customerModel;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Customer $customerModel,
        \Ced\Wallet\Helper\Data $helper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_customerModel = $customerModel;
        $this->_session = $session;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $isEnabled = $this->helper->isModuleEnabled();
        if (!$isEnabled) {
            $this->_redirect("*/*");
        }
        $resultJson = $this->resultJsonFactory->create();

        /*check if customer is logged in or not*/
        if(!$this->_session->isLoggedIn()) {
            $resultJson->setData('please login to transfer money');
            return $resultJson;
        }

        if($email = $this->getRequest()->getParam('email')){
            $resultJson = $this->validateEmail($email, $resultJson);
        }

        if($amount = $this->getRequest()->getParam('amount')){
            $resultJson = $this->validateAmount($amount, $resultJson);
        }

        if(!($resultJson)) {
            $resultJson->setData('Incorrect Data, please check again.');
        }
        return $resultJson;
    }
    
    public function validateAmount($amount, $resultJson){
        $customer = $this->_session->getCustomer();
        $helper = $this->_objectManager->get(\Ced\Wallet\Helper\Data::class);
        $walletAmount = $helper->convert($customer->getAmountWallet());
        $transferAmount = $amount;

        if (($transferAmount > $walletAmount) || ($transferAmount <= 0)){
            $txt = ' Entered amount '.$transferAmount.' is more than the avaliable amount '.$walletAmount.' of wallet.';
        }else{
            $txt = true;
        }
        
        $resultJson->setData($txt);
        return $resultJson;
    }
    public function validateEmail($email, $resultJson){
        $customerEmail = $this->_session->getCustomer()->getEmail();
        /*check if emails are same*/
        if ($email == $customerEmail) {
            $resultJson->setData("you can't transfer money to your own email-id");
            return $resultJson;
        }

        $customerData = $this->_customerModel->getCollection()
            ->addFieldToFilter('email', $email)
            ->addFieldToFilter('enable_wallet_system', 1);

        if(!count($customerData)) {
            $resultJson->setData('This email not avaliable, please check again.');
        } else {
            $resultJson->setData('true');
        }
        return $resultJson;
    }
}