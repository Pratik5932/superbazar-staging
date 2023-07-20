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

class Sendotp extends Action
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
        \Magento\Customer\Model\Customer $customerModel
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_customerModel = $customerModel;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
        $result = false;
        /*check if customer is logged in or not*/
        if(!$customerSession->isLoggedIn()) {
            $resultJson->setData(
                [
                    'status'=> false,
                    'message'=>__('Please login to transfer money'),
                ]
            );
            return $resultJson;
        }else {
            
            $avaliableAmount = $customerSession->getCustomer()->getAmountWallet();
            $requestedAmount = $this->getRequest()->getParam('amount');
            $receiverEmail = $this->getRequest()->getParam('email');
            
            $customerData = $this->_customerModel->getCollection()
                    ->addFieldToFilter('email', $receiverEmail)
                    ->addFieldToFilter('enable_wallet_system', 1);
            if(!count($customerData)) {
                $resultJson->setData([
                    'status'=> false,
                    'message'=>__('This email not avaliable, please check again.')
                ]);
                 return $resultJson;
            }
            
            if($receiverEmail == $customerSession->getCustomer()->getEmail()){
                $resultJson->setData([
                    'status'=> false,
                    'message'=>__('You can\'t transfer money to your own email-id.')
                ]);
                return $resultJson;
            }
            
            if($requestedAmount>$avaliableAmount){
               
                $resultJson->setData(
                    [
                        'status'=> false,
                        'message'=> __('Amount not available for transfer.'),
                    ]
                );
                return $resultJson;
            }
            
            $email = $customerSession->getCustomer()->getEmail();
            $name = $customerSession->getCustomer()->getName();
            $otpEmail = $this->_objectManager->get('Ced\Wallet\Helper\Email');
            $o_email = $otpEmail->sendOtp($email, $name);
            $result = $o_email['result'];
            $otp = $o_email['otp'];
            $customerSession->setWalletOtpValidationNumber($otp);
            $customerSession->setWalletOtpVerified(false);

            $resultJson->setData(
                [
                    'status'=> true,
                    'message'=> 'Otp is send to '.$result,
                ]
            );
            return $resultJson;
        } 
    }
}