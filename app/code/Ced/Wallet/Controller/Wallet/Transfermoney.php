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

use Braintree\Exception;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Ced\Wallet\Model\TransactionFactory;

class Transfermoney extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;
    /** 
     * @var PageFactory $resultPageFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var TransactionFactory
     */
    protected $_walletTransaction;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        TransactionFactory $walletTransaction,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Customer\Model\CustomerFactory $_customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ced\Wallet\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_dateTime = $dateTime;
        $this->_walletTransaction = $walletTransaction;
        $this->_customerFactory = $_customerFactory;
        $this->_session = $session;
        $this->helper = $helper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $isEnabled = $this->helper->isModuleEnabled();
        if (!$isEnabled) {
            $this->_redirect("*/*");
        }
        
        try{
            /*
            * for server side otp validation
            * set in Ced\Wallet\Controller\Wallet\Verifyotp and Ced\Wallet\Controller\Wallet\Sendotp 
            */
            if ($this->_objectManager->create(\Ced\Wallet\Helper\GenerateOTP::class)->otpEnabled()){
                if (!$this->_session->getWalletOtpVerified()){
                    throw new \Magento\Setup\Exception(__('Something went wrong, please try again'));
                }
            }

            if($this->_session->isLoggedIn()) {
                $priceHelper = $this->_objectManager->create('Magento\Framework\Pricing\Helper\Data');
                $wesiteId = $this->storeManager->getStore()->getWebsiteId();
                $avaliableAmount = $this->_session->getCustomer()->getAmountWallet();
                $amount = floatval($this->getRequest()->getParam('amount'));
               
                $baseToCurrent = $this->_objectManager->create('Ced\Wallet\Helper\Data')->convert(1); 
                $amount = floatval($amount/$baseToCurrent);

                $description = $this->getRequest()->getParam('description');
                $description = (isset($description))?$description:' ';

                /*add data in wallet transation table*/
                $customerEmail = $this->_session->getCustomer()->getEmail();

                $toEmail = $this->getRequest()->getParam('email');

                if ($customerEmail == $toEmail){
                    throw new \Exception(__('Wallet transfer for same account is not allowed.'));
                }
                
                if($avaliableAmount < floatval($amount)){
                    throw new \Exception(__('Amount %1 is greater than the avaliable amount in your wallet.', $priceHelper->currency($amount, true, false)));
                }

                $wallet = $this->_walletTransaction->create();
                $transfer = $wallet->walletTransfer(
                    $customerEmail, 
                    $toEmail, 
                    $amount, 
                    'Wallet-Transfer', 
                    $description,
                    $wesiteId
                );

                if ($transfer){
                     $this->messageManager->addSuccessMessage(__('Amount %1 is transferred To Wallet successfully', $priceHelper->currency($amount, true, false)));
                }else{
                    throw new \Exception(__('Unable to Transfer Amount'));
                }

            }else{
                throw new \Exception(__('Please logged in again, to proceed.'));
            }

            return $this->_redirect('wallet/wallet/transaction');
        }catch(\Exception $e){
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('wallet/wallet/wallettransfer');
        }
    }
}