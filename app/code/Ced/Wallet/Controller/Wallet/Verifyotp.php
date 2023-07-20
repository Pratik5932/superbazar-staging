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

class Verifyotp extends Action
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
        $return = false;    
        if($customerSession->isLoggedIn()) { 
            if ($reg_otp = $customerSession->getWalletOtpValidationNumber()){
               
                $post_otp = $this->getRequest()->getPostValue('otp_number', null);
                
                if ($reg_otp  == $post_otp) {
                    $return = true; 
                }
            }
        } 
        /*for server side otp validation*/
        if ($return) {
            $customerSession->setWalletOtpVerified(true);
        }else{
            $customerSession->setWalletOtpVerified(false);
        }

        $resultJson->setData($return);
        return $resultJson;
    }
}