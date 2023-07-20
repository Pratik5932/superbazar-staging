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

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Setwallet extends \Magento\Customer\Controller\AbstractAccount
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
	 * @param Context $context
	 * @param PageFactory $resultPageFactory
	 */
	protected $resultJsonFactory;
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Ced\Wallet\Helper\Data $helper
	) {
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
		$this->resultJsonFactory = $resultJsonFactory;
		$this->checkoutSession = $checkoutSession;
		$this->helper = $helper;
	}

	/**
	 *
	 * @return \Magento\Framework\View\Result\Page
	 */
	public function execute()
	{
	    $isEnabled = $this->helper->isModuleEnabled();
        if (!$isEnabled) {
            $this->_redirect("*/*");
        }
        
		$data = $this->getRequest()->getParam('grandtotal');
		$status = $this->getRequest()->getParam('getwallet');
		$customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
		
		if($customerSession->isLoggedIn()) {
		    $customerSession->setWalletStatus($status);
		    $amount = $customerSession->getCustomer()->getAmountWallet();
		    if($amount == $data){
		       $leftamount = 0;
		    }elseif($amount >$data){
		    	$leftamount = $amount -$data;
		    }else {
		      $leftamount = $data-$amount;
		    }
		}
		
		if($status =='select'){
		    $this->checkoutSession->setWalletLeftAmount($leftamount);
		}else{
		    $this->checkoutSession->unsWalletLeftAmount();
		}
	
		
        $leftamount = number_format((float)$leftamount, 2, '.', '');
		$resultJson = $this->resultJsonFactory->create();
		$resultJson->setData($leftamount);
		return $resultJson;
	}
}