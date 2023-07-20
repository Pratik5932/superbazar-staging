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

class Transaction extends \Magento\Framework\App\Action\Action
{
	protected $resultPageFactory;
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory,
		\Ced\Wallet\Helper\Data $helper
	) {
		$this->resultPageFactory = $resultPageFactory;
		$this->helper = $helper;
		parent::__construct($context);
	}


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
	public function execute()
	{
        $isEnabled = $this->helper->isModuleEnabled();
        if (!$isEnabled) {
            $this->_redirect("*/*");
        }
        
		if (!($this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn())) { 
			return $this->_redirect('customer/account/login');
		}
		$cid = $this->_objectManager->get('Magento\Customer\Model\Session')->getId();
		if($cid)
		{
		    $customer = $this->_objectManager->get("Magento\Customer\Model\Customer")->load($cid);
		    if(!intval($customer->getEnableWalletSystem())){
		        return $this->_redirect('customer/account/index');
		    }
		}
		$resultRedirect = $this->resultPageFactory->create();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Manage Wallet'));
        return $resultRedirect;
	}

}
