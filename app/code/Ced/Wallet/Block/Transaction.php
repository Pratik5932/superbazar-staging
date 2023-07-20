<?php
namespace Ced\Wallet\Block;
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
class Transaction extends \Magento\Backend\Block\Widget\Grid\Container
{


    protected $_template = 'Ced_Wallet::wallet/settings.phtml';
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'wallet_transaction';
        $this->_blockGroup = 'Ced_Wallet';
        $this->_headerText = __('Wallet');
        parent::_construct();
        //$this->removeButton('add');
        // $this->setData('area','adminhtml');

    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Ced\Wallet\Block\Transaction\Grid', 'ced_wallet_transaction_grid')
        );
        return parent::_prepareLayout();
    }


    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }


    public function additionalWalletButtons()
    {
        return '';
    }


}