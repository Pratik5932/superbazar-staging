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

class Requestmoney extends \Magento\Customer\Controller\AbstractAccount
{
	/**
	 * @var PageFactory
	 */
	protected $resultPageFactory;

	/**
	 * @param Context $context
	 * @param PageFactory $resultPageFactory
	 */
	public $_dataHelper;

	protected $resultJsonFactory;

	public function __construct(
			Context $context,
			PageFactory $resultPageFactory,
			\Ced\Wallet\Helper\Data  $dataHelper,
			\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
	) {
		parent::__construct($context);
		$this->_dataHelper = $dataHelper;
		$this->resultPageFactory = $resultPageFactory;
		$this->resultJsonFactory = $resultJsonFactory;
	}

	/**
	 *
	 * @return \Magento\Framework\View\Result\Page
	 */
	public function execute()
	{
	    $isEnabled = $this->_dataHelper->isModuleEnabled();
        if (!$isEnabled) {
            $this->_redirect("*/*");
        }
        
		$data = $this->getRequest()->getParams();

		$baseCurrencyCode = $this->_objectManager->get('Magento\Directory\Helper\Data')->getBaseCurrencyCode();
		$baseToQuoteRate = $this->_objectManager->create('\Magento\Directory\Helper\Data')->currencyConvert(1, $baseCurrencyCode);
		$amount = floatval($data['amount'])/$baseToQuoteRate;
		
		try{




		    if($amount > ($this->_dataHelper->getWalletAmount()-$this->_dataHelper->getRequestedAmount())){
		       $this->messageManager->addErrorMessage(__('Insufficient funds for request.'));
		        return $this->_redirect('wallet/wallet/transaction/');
		    }
			$request = $this->_objectManager->create('Ced\Wallet\Model\Request');
			$request->setAmount($amount);
			$request->setStatus(\Ced\Wallet\Model\Request::PENDING);
			$request->setCustomerId($data['customer_id']);
			$request->setDetails($data['details']);
			$request->save();

            $this->messageManager->addSuccessMessage(__('You Successfully requested for money'));
		    return $this->_redirect('wallet/wallet/transaction/');
		}catch(\Exception $e){
			$this->messageManager->addErrorMessage(__($e->getMessage()));
		    return $this->_redirect('wallet/wallet/transaction/');
			 
	    }
	     return $this->_redirect('wallet/wallet/transaction/');
	}
}