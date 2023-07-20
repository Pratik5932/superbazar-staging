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

namespace Ced\Wallet\Controller\Adminhtml\Wallet;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Customer;

class Maketransaction extends \Magento\Backend\App\Action
{
	/**
     * @var PageFactory
     */
    protected $resultPageFactory;
 
    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */

    protected $_customerRepository;

    public function __construct(
        Context $context,
        Customer $customerRepository,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_customerRepository = $customerRepository;
        $this->resultPageFactory = $resultPageFactory;
    }
 
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $customerId = (int)$this->getRequest()->getParam('id');

        if(!$customerId || $customerId==0 || !$customerData = $this->_customerRepository->load($customerId)){
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('wallet/wallet/transactions');
            return $resultRedirect;
        }

        $registry = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Registry');
        if(!$registry->registry('customer_wallet')) {
            $registry->register('customer_wallet',$customerData);
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Credit/Debit Wallet')));
        return $resultPage;
    }
}