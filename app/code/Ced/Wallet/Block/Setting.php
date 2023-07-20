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

namespace Ced\Wallet\Block;
use Magento\Customer\Model\Session;

class Setting extends \Magento\Framework\View\Element\Template
{
    /** @var Session */
    public $session;

    public $_objectManager;
    
    protected $_gridFactory;
    
    /**
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
    	\Ced\Wallet\Model\TransactionFactory $gridFactory,
        Session $customerSession,
    	array $data = []
    ) {
    	$this->_gridFactory = $gridFactory;
        parent::__construct($context, $data);
        $this->session = $customerSession;
        $this->_objectManager=$objectManager;
        $this->pageConfig->getTitle()->set(__('My Wallet'));

        $customerId = $this->session->getId();
        $collection = $this->_gridFactory->create()->getCollection()->addFieldToFilter('customer_id',$customerId);
        $this->setCollection($collection);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->_filtercollection) {
            if ($this->_filtercollection->getSize() > 0) {
                if ($this->getRequest()->getActionName() == 'index') {
                    $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'custom.pager');
                    $pager->setAvailableLimit(array(5 => 5, 10 => 10, 20 => 20, 'all' => 'all'));
                    $pager->setCollection($this->_filtercollection);
                    $this->setChild('pager', $pager);
                }
            }
        }
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPagerHtml()
    {
    	return $this->getChildHtml('pager');
    }
    
    /*
     * get amount in customer wallet
    * @return float
    */
      public function getWalletAmount(){
    	$customerId = $this->session->getId();
    	$customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
    	$amount = $customer->getAmountWallet();
    	return $amount;
    }  
}
