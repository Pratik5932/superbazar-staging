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
namespace Ced\Wallet\Block\Adminhtml\Transaction\Edit;


class Customer extends \Magento\Backend\Block\Widget\Tab
{
    public $_currencyHelper;
    public $_template = 'Ced_Wallet::transaction_form_customer.phtml';
    public $_objectManager;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $currencyHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_currencyHelper = $currencyHelper;
        parent::__construct($context, $data);
    }

    public function getCustomerWalletAmount(){
        $registry = $this->_objectManager->get('Magento\Framework\Registry');
        $walletAmount = 0;
        if($customerData = $registry->registry('customer_wallet')) {
            $walletAmount = $customerData->getData('amount_wallet')?$customerData->getData('amount_wallet'):0;
        } else {
            $walletAmount = 0;
        }       

        return $this->_currencyHelper->currency($walletAmount,true,false);        
    }

}
